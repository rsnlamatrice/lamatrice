var Autocompletor = (function() {
    /*********************** KeywordList Class *****************************/

    function KeywordList(keywords) {
        this.keywords = this.transformListArray(keywords);
        this.keywordLimit = 0;
        this.ac = null;
    }

    KeywordList.prototype.updateList = function (keywords) {
        this.keywords = this.transformListArray(keywords);
    }

    KeywordList.prototype.transformListArray = function (list, k_prefix, k_sufix, dk_prefix, dk_sufix) {
        newList = [];
        k_prefix = (typeof k_prefix !== 'string') ? '' : k_prefix;
        k_sufix = (typeof k_sufix !== 'string') ? '' : k_sufix;
        dk_prefix = (typeof dk_prefix !== 'string') ? '' : dk_prefix;
        dk_sufix = (typeof dk_sufix !== 'string') ? '' : dk_sufix;
        if (list && list.constructor == Array) {
            for (var i = 0; i < list.length; ++i) {
                newList.push({'keyword': k_prefix + list[i] + k_sufix, 'displayed_keyword': dk_prefix + list[i]+ dk_sufix});
            }
        }

        return newList;
    }

    KeywordList.prototype.retrieveKeywords = function (callback) {
        //tmp methode to overload if needed
        callback(this.keywords);
    }

    KeywordList.prototype.getFilteredList = function (filter, caseSensitive, callback) {
        var self = this,
            matchingWords = [];
        this.retrieveKeywords(function(keywords) {
            if (keywords.constructor == Array) {
                for (var i = 0; i < keywords.length; ++i) {
                    if (keywords[i].displayed_keyword != filter) {
                        var index1, index2;//tmp index2
                        if (caseSensitive) {
                            index1 = keywords[i].keyword.indexOf(filter);
                            index2 = keywords[i].displayed_keyword.indexOf(filter);
                        } else {
                            index1 = keywords[i].keyword.toUpperCase().indexOf(filter.toUpperCase());
                            index2 = keywords[i].displayed_keyword.toUpperCase().indexOf(filter.toUpperCase());
                        }

                        index = (Math.min(index1, index2));
                        index = (index == -1) ? (Math.max(index1, index2)) : index1;

                        if (index != -1) {
                            matchingWords.push({'keyword': keywords[i].keyword, 'displayed_keyword': keywords[i].displayed_keyword, 'index': index}); 
                        }
                    }
                }

                callback(self.getFilteredListFromMatchingWord(matchingWords));
            } else {
                console.log("bad not array");
            }
        });
    }

    KeywordList.prototype.getFilteredListFromMatchingWord = function (matchingWords) {
        var wordList = [];

        matchingWords.sort(function(a, b) {
            if (a.index == b.index) {
                var wordA = a.keyword.toUpperCase(),
                    wordB = b.keyword.toUpperCase();
                if (wordA > wordB) {
                    return 1;
                } else if (wordA < wordB) {
                    return -1;
                }

                return 0;
            }

            return a.index - b.index;
        });

        for (var i = 0; i < matchingWords.length && (this.keywordLimit <= 0 || i < this.keywordLimit); ++i) {
            wordList.push(matchingWords[i]);
        }

        return wordList;
    }

    /*********************** AutoComplete Class *****************************/

    function AutoComplete() {
            this.keywordList = null;
            this.textzone = null;
            this.currentDomList = null;
            this.currentSelected = -1;
            this.currentWordData = null;
            this.allwaysBlockTab = true;
            this.neverBlockTab = false;
            this.separators = ['.', '+', '-', '*', '/', '=', '<', '>', '!', '(', ')', '\'', '"'];
            this.blankChars = [' ', '\n', '\t'];
            this.minChar = 1;
            this.wordList = [];
    }

    AutoComplete.prototype.init = function (element, keywordList) {
        var self = this;
        this.textzone = element;
        this.textzone.setAttribute('spellcheck', false);
        this.currentWordData = this.getCurentWordData();
        keywordList = (keywordList.constructor == Array) ? new KeywordList(keywordList) : keywordList;
        this.keywordList = (typeof keywordList === 'object') ? keywordList : new KeywordList();
        this.keywordList.ac = this;

        this.textzone.addEventListener("keyup", function (e) { self.onClickOrKeyUp(e); });
        this.textzone.addEventListener("click", function (e) { self.onClickOrKeyUp(e); });
        this.textzone.addEventListener("keydown", function (e) { self.onKeyDown(e); });
        window.addEventListener('scroll', function (e) { self.resizeList(self.currentDomList); });
        window.addEventListener('resize', function (e) { self.resizeList(self.currentDomList); });
        document.addEventListener("mouseup",     function (e) { self.resizeList(self.currentDomList); });
        this.textzone.addEventListener("focusout", function (e) {
            setTimeout(function() {//tmp time out -> else the clearing is triggered first on click on the keyword list...
                self.clearList();
            }, 200);
        });
    }

    AutoComplete.prototype.onClickOrKeyUp = function (e) {//tmp funciton name
        if (this.currentDomList == null || (e.keyCode != 38 && e.keyCode != 40)) {
            this.currentWordData = this.getCurentWordData();

            if (this.currentWordData.value.length >= this.minChar) {
                this.displayFilterList();
            } else if (e.keyCode != 9){
                this.clearList();
            }
        }
    }

    AutoComplete.prototype.displayFilterList = function() {//tmp name !!
        var self = this;
        this.keywordList.getFilteredList(this.currentWordData.value, false, function(wordList) {
            if (wordList.length > 0) {
                self.wordList = wordList;
                if (self.currentDomList == null) {
                    self.currentDomList = self.createHtmlList(wordList);
                    self.textzone.parentNode.insertBefore(self.currentDomList, self.textzone.nextSibling);
                } else {
                    var newDomList = self.createHtmlList(wordList);//tmp
                    self.textzone.parentNode.replaceChild(newDomList, self.currentDomList);
                    self.currentDomList = newDomList;
                }

                self.currentSelected = 0;
                self.getSelectedDomElement().setAttribute('class', 'selected');
            } else {
                self.clearList();
            }
        });
    }

    AutoComplete.prototype.getSelectedDomElement = function() {
        if (this.currentSelected != -1) {
            return this.currentDomList.getElementsByTagName('li')[this.currentSelected];
        }

        return null;
    }

    AutoComplete.prototype.getSelectedWord = function() {
        var domElement = this.getSelectedDomElement();

        if (domElement != null) {
            return domElement.getAttribute('value');
        }

        return "";
    }

    AutoComplete.prototype.setSelectedKeyword = function (keywordId, scroll) {
        this.getSelectedDomElement().setAttribute('class', '');
        var previousSelected = this.currentSelected;
        this.currentSelected = keywordId;
        var selectedDomElement = this.getSelectedDomElement();
        selectedDomElement.setAttribute('class', 'selected');
        //this.currentDomList.scrollTo(0, 100);
        if (scroll) {
            var elemHeight = selectedDomElement.getBoundingClientRect().height;
            if (this.currentSelected == 0) {
                this.scrollListTo(0);
            } else if (this.currentSelected == (this.wordList.length - 1)) {
                this.scrollListTo(elemHeight * this.currentSelected);
            } else {
                if (this.currentSelected > previousSelected) {
                    //move down
                    var suposedListScollTop = elemHeight * (previousSelected + 1) - this.currentDomList.getBoundingClientRect().height;
                    if ((this.currentDomList.scrollTop - elemHeight) <= suposedListScollTop) {
                        this.scrollListTo(elemHeight * (this.currentSelected + 1) - this.currentDomList.getBoundingClientRect().height);
                    }
                } else {
                    //move up
                    var suposedListScollTop = elemHeight * previousSelected;
                    if ((this.currentDomList.scrollTop + elemHeight) >= suposedListScollTop) {
                        this.scrollListTo(elemHeight * this.currentSelected);
                    }
                }
            }
        }
    }

    AutoComplete.prototype.selectNextKeyword = function() {
        var nextKeyword = this.currentSelected + 1;
        nextKeyword = (nextKeyword >= this.currentDomList.getElementsByTagName('li').length) ? 0 : nextKeyword;//tmp length !!
        this.setSelectedKeyword(nextKeyword, true);
    }

    AutoComplete.prototype.selectPreviousKeyword = function() {
        var previousKeyword = this.currentSelected - 1;
        previousKeyword = (previousKeyword < 0) ? this.currentDomList.getElementsByTagName('li').length - 1 : previousKeyword;
        this.setSelectedKeyword(previousKeyword, true);
    }

    AutoComplete.prototype.onKeyDown = function (e) {
        var blockEvent = false;

        if (this.currentDomList != null) {
            // up/down = 38/40
            if (e.keyCode == 38) {
                blockEvent = true;
                this.selectPreviousKeyword();
            } else if (e.keyCode == 40) {
                blockEvent = true;
                this.selectNextKeyword();
            }
        }

        if ((e.keyCode == 9 || e.keyCode == 13) && this.currentSelected >= 0) {
            //when tab or enter is pressed
            blockEvent = true;
            this.autocomplete(this.getSelectedWord(), false);
            this.clearList();
        }

        if (this.allwaysBlockTab && e.keyCode == 9) {
            blockEvent = true;
            this.curentWordData = this.getCurentWordData();
            this.displayFilterList();
        }

        if (this.neverBlockTab && e.keyCode == 9) {
            return true;
        }

        if (blockEvent) {
            e.preventDefault();
            return false;
        }
    }

    AutoComplete.prototype.onClickOnKeyword = function (e) {
        var element = e.target;
        this.textzone.focus();
        this.autocomplete(element.getAttribute('value'), false);
        this.clearList();
    }

    AutoComplete.prototype.createHtmlList = function (list) {
        var htmlList = document.createElement('ul');
        htmlList.setAttribute('class', 'autocompleteList');
        var rect = this.textzone.getBoundingClientRect();
        this.resizeList(htmlList);
        for (var i = 0; i < list.length; ++i) {
            htmlList.appendChild(this.createListElement(list[i], i));
        }

        // for(var key in list) {
        //     var value = list[key];
        //     document.write(key + " = " + value + '<br>');
        //     htmlList.appendChild(this.createListElement(list[key]));
        // }

        var self = this;
        htmlList.addEventListener('mousewheel', function (e) { self.scrollList(self.getWheelDelta(e) * -10); e.preventDefault(); return false; });
        htmlList.addEventListener('DOMMouseScroll', function (e) { self.scrollList(self.getWheelDelta(e) * -10); e.preventDefault(); return false; });

        return htmlList;
    }

    AutoComplete.prototype.resizeList = function (htmlList) {
        if (htmlList != null) {
            var rect = this.textzone.getBoundingClientRect();
            htmlList.style.top = (rect.top + rect.height) + 'px';
            htmlList.style.left = rect.left + 'px';
            htmlList.style.width = rect.width + 'px';
        }
    }

    AutoComplete.prototype.getWheelDelta = function (e) {
        //return Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
        if (e.wheelDelta) {
            return e.wheelDelta / 120;
        } else if (e.detail) {
            return -e.detail;
        }

        return 0;
    }

    AutoComplete.prototype.scrollList = function (delta) {
        this.currentDomList.scrollTop += delta;
    }

    AutoComplete.prototype.scrollListTo = function (pos) {//tmp use it !
        this.currentDomList.scrollTop = pos;
    }

    AutoComplete.prototype.createListElement = function (value, id) {
        var self = this;
        listElement = document.createElement('li');
        listElement.textContent = value.keyword;
        listElement.setAttribute('value', value.displayed_keyword);
        listElement.addEventListener('click', function (e) {
            self.onClickOnKeyword(e);
        });

        return listElement;
    }

    AutoComplete.prototype.getCaretPosition = function (ctrl) {//tmp
        var CaretPos = 0; 
        if (document.selection) {
            // IE Support
            ctrl.focus();
            var Sel = document.selection.createRange();
            Sel.moveStart('character', -ctrl.value.length);
            CaretPos = Sel.text.length;
        } else if (ctrl.selectionStart || ctrl.selectionStart == '0') {
            // Firefox support
            CaretPos = ctrl.selectionStart;
        }

        return (CaretPos);
    }

    AutoComplete.prototype.setCaretPosition = function (ctrl, pos) {//tmp
        ctrl.setSelectionRange(pos, pos);
    }

    AutoComplete.prototype.isSeparator = function (character) {
        return (this.isBlankChar(character) || this.separators.indexOf(character) != -1);
    }

    AutoComplete.prototype.isBlankChar = function (character) {
        return (this.blankChars.indexOf(character) != -1);
    }

    AutoComplete.prototype.getCurentWordData = function () {
        return this.getWordDataAt(this.getCaretPosition(this.textzone));
    }

    AutoComplete.prototype.getWordDataAt = function (pos) {//tmp
        var content = this.textzone.value;
        var begin = 0, end = 0;

        for (var i = pos; i > 0 && !this.isSeparator(content[i - 1]); --i);
        begin = i;

        for (var i = pos; i < content.length && !this.isSeparator(content[i]); ++i);
        end = i;

        return {'begin': begin, 'end': end, 'value': content.substring(begin, end)};
    }

    AutoComplete.prototype.getCharPrecedingWord = function(wordData) {
        var content = this.textzone.value,
            pos = wordData.begin - 1;

        if (pos >= 0) {
            return content[pos];
        }

        return '';
    }

    AutoComplete.prototype.replace = function (str, begin, end, newValue) {
        return str.substring(0, begin) + newValue + str.substring(end);
    }

    AutoComplete.prototype.autocomplete = function (newWord, addSpace) {
        var content = this.textzone.value;
        var newPos = this.currentWordData.begin + newWord.length;

        if (addSpace) {
            ++newPos;
            newWord += ' ';
        }

        this.textzone.value = this.replace(content, this.currentWordData.begin, this.currentWordData.end, newWord);
        this.setCaretPosition(this.textzone, newPos);
    }

    AutoComplete.prototype.clearList = function () {
        this.wordList = [];
        if (this.currentDomList != null) {
            this.textzone.parentNode.removeChild(this.currentDomList);
            this.currentDomList = null;
            this.currentSelected = -1;
        }
    }

    //////////////////////////////// SELECT Element //////////////////////////////////

    //tmp on modification on select dom element (ex: )
    function AutoCompleteFromSelect() {//tmp name
        AutoComplete.apply(this);
        this.htmlSelectElement = null;
        this.selectedHtmlOption = null;
        this.allwaysBlockTab = false;//tmp 2 bool ...
        this.neverBlockTab = true;
        this.allowNewOption = false;
        this.placeholder = '';
        this.minChar = 0;
    }

    AutoCompleteFromSelect.prototype = Object.create(AutoComplete.prototype);


    AutoCompleteFromSelect.prototype.init = function (htmlSelectElement, placeholder) {//tmp add boolean for add new values !
        var self = this;
        this.htmlSelectElement = htmlSelectElement;
        var firstChild = this.htmlSelectElement.querySelector("option"),
            selectedElement = this.htmlSelectElement.querySelector("option[selected]");
        this.placeholder = (typeof placeholder === "string") ? placeholder : ((firstChild.disabled) ? firstChild.value : "");// tmp first child not necessary for the entire list ....

        this.htmlSelectElement.setAttribute('hidden', 'hidden');
        var input = document.createElement('input');
        input.setAttribute('type', 'text');
        input.setAttribute('placeholder', this.placeholder);
        htmlSelectElement.parentNode.insertBefore(input, htmlSelectElement);
        AutoComplete.prototype.init.call(this, input, new KeywordList(this.getSelectOptions()));
        this.selectedHtmlOption = (selectedElement == null || selectedElement.disabled) ? null : selectedElement;
        this.textzone.value = this.getCurentSelectedValue();

        this.textzone.addEventListener("focusout", function (e) { self.setSelectedOptionFromValue(self.textzone.value); });
        //this.htmlSelectElement.addEventListener("DOMNodeInserted", function (e) { self.updateList(); });
        this.htmlSelectElement.addEventListener("DOMSubtreeModified", function (e) { self.updateList(); });
    }

    AutoCompleteFromSelect.prototype.updateList = function() {
        this.keywordList.updateList(this.getSelectOptions());
    }

    AutoCompleteFromSelect.prototype.getSelectOptions = function () {//tmp name
        var htmlOptions = this.htmlSelectElement.getElementsByTagName('option'),
            options = [];

            for (var i = 0; i < htmlOptions.length; ++i) {
                if (!htmlOptions[i].disabled) {
                    options.push(htmlOptions[i].textContent);
                }
            }

            return options;
    }

    AutoCompleteFromSelect.prototype.getCurentWordData = function () {
        var content = this.textzone.value;
        return {'begin': 0, 'end': content.length, 'value': content};
    }

    AutoCompleteFromSelect.prototype.getCurentSelectedValue = function() {
        if (this.selectedHtmlOption) {
            return this.selectedHtmlOption.textContent;
        }

        return '';
    }

    AutoCompleteFromSelect.prototype.setSelectedOptionFromValue = function(value, firstCall) {
        var htmlOptions = this.htmlSelectElement.getElementsByTagName('option');
        firstCall = (typeof firstCall !== 'bool') ? true : firstCall;
        value = (typeof value !== 'string') ? '' : value;

            for (var i = 0; i < htmlOptions.length; ++i) {
                if (htmlOptions[i].textContent == value) {
                    //this.selectedHtmlOption.selected = "";
                    htmlOptions[i].selected = true;
                    this.selectedHtmlOption = htmlOptions[i];
                    return;
                }
            }

            if (value != '' && this.allowNewOption && typeof this.addNewOption === 'function' && firstCall) {
                console.log('tmp: add new option: ' + value);
                var self = this;
                this.addNewOption(value, function(addedValue) {
                    if (typeof addedValue === 'string' && addedValue != '') {
                        self.textzone.value = addedValue;
                        self.setSelectedOptionFromValue(addedValue, false);//tmp
                    } else {
                        self.textzone.value = self.getCurentSelectedValue();
                    }
                    //return this.addNewOption();//tmp implement that !!
                });
                return;
            }
            //this.textzone.value = this.getCurentSelectedValue();//tmp check boolean (can add new option?)!! //tmp do not do that here -> double add word with real autocomplete func ...
    }

    AutoCompleteFromSelect.prototype.setAllowNewOption = function(allow, addNewOption) {
        this.allowNewOption = allow;

        if (this.allowNewOption) {
            this.addNewOption = addNewOption;
        }
    }

    AutoCompleteFromSelect.prototype.autocomplete = function(newWord) {
        this.setSelectedOptionFromValue(newWord);

        AutoComplete.prototype.autocomplete.call(this, newWord, false);
    }

    ////////////////////// Initialisation ///////////////////////////////////

    function init() {
        // TMP : auto initialyse some element !!! -> method to complete (search field auto using html dom list !!) // TMP: do the same for radio button than for select
        var selects = document.getElementsByClassName('autocompletor');// TMP !!!!!!!!!
        Array.prototype.forEach.call(selects, function(select) {
            ac = new Autocompletor.AutoCompleteFromSelect;
            ac.init(select);
        });
    }

    function addLoadListener(func) {
        if (window.addEventListener) {
            window.addEventListener("load", func, false);
        } else if (document.addEventListener) {
            document.addEventListener("load", func, false);
        } else if (window.attachEvent) {
            window.attachEvent("onload", func);
        }
    }

    addLoadListener(init);

    /////////////////// Return Library members !! ///////////////////////////////

    // Membres publics
    return {
        "KeywordList": KeywordList,
        "AutoComplete": AutoComplete,
        "AutoCompleteFromSelect": AutoCompleteFromSelect
    };
})();