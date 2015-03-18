{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
<!DOCTYPE html>
<html>
	<head>
		<title>{$PAGETITLE}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- for Login page we are added -->
		<link href="libraries/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="libraries/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="libraries/bootstrap/css/jquery.bxslider.css" rel="stylesheet" />
		<script src="libraries/jquery/jquery.min.js"></script>
		<script src="libraries/jquery/boxslider/jquery.bxslider.js"></script>
		<script src="libraries/jquery/boxslider/jquery.bxslider.min.js"></script>
		<script src="libraries/jquery/boxslider/respond.min.js"></script>
		<script>
			jQuery(document).ready(function(){
				scrollx = jQuery(window).outerWidth();
				window.scrollTo(scrollx,0);
				slider = jQuery('.bxslider').bxSlider({
				auto: true,
				pause: 4000,
				randomStart : true,
				autoHover: true
			});
			jQuery('.bx-prev, .bx-next, .bx-pager-item').live('click',function(){ slider.startAuto(); });
			}); 
		</script>
	</head>
	<body>
		<div class="container-fluid login-container">
			<div class="row-fluid">
				<div class="span3">
					<div class="logo"><a target="_blank" href="https://www.sortirdunucleaire.org/"><img src="test/logo/logo-sdn.gif"/></a>
					</div>
				</div>
				<div class="span9">
					<div class="helpLinks">
						<a href="http://www.sortirdunucleaire.org/">Sortir du Nucl&eacute;aire</a> | 
						<a href="https://groupware.sortirdunucleaire.org/">Groupware</a> | 
						<a href="https://echanges.sortirdunucleaire.org">Intranet</a> | 
						<a href="https://webmail.gandi.net/">Webmail</a>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="content-wrapper">
						<div class="container-fluid">
							<div class="row-fluid">
								<div class="span6">
									<div class="carousal-container">
										<div><h2>&nbsp;</h2></div>
										<ul class="bxslider">
											<li>
												<div id="slide01" class="slide">
													<img class="pull-left" src="http://www.sortirdunucleaire.org/squelettes/images/une-cartes.jpg"/>
													<img class="pull-right" src="http://www.sortirdunucleaire.org/squelettes/stop-rafistolage/bando-stop-rafistolage.png"/>
												</div>
											</li>
											<li>
												<div id="slide02" class="slide">
													<img class="pull-left" src="http://boutique.sortirdunucleaire.org/101-home_default/changeons-dere-sortons-du-nucleaire.jpg"/>
													<img class="pull-right" src="layouts/vlayout/modules/RSN/resources/images/Lamatigre.small.png"/>
												</div>
											</li>
											<li>
												<div id="slide03" class="slide">
													<img class="pull-left" src="http://boutique.sortirdunucleaire.org/172-home_default/masques-contre-le-deni-x5.jpg"/>
													<img class="pull-right" src="http://boutique.sortirdunucleaire.org/182-home_default/autocollant-bure-stop.jpg"/>
												</div>
											</li>
											<li>
												<div id="slide04" class="slide">
													<img class="pull-left" src="http://boutique.sortirdunucleaire.org/222-home_default/par-ici-la-sortie-du-nucleaire.jpg"/>
													<img class="pull-right" src="http://www.sortirdunucleaire.org/squelettes/stop-rafistolage/bando-stop-rafistolage.png"/>
												</div>
											</li>
											<li>
												<div id="slide05" class="slide">
													<img class="pull-left" src="http://boutique.sortirdunucleaire.org/93-home_default/autocollant-nucleaire-non-merci-en-japonais.jpg"/>
													<img class="pull-right" src="http://boutique.sortirdunucleaire.org/25-home_default/revue-silence-n-410-agonie-du-nucleaire.jpg"/>
												</div>
											</li>
											<li>
												<div id="slide05" class="slide">
													<img class="pull-left" src="layouts/vlayout/modules/RSN/resources/images/Lamatigre.small.png"/>
													<img class="pull-right" src="http://boutique.sortirdunucleaire.org/85-home_default/5-autocollants-tchernobyl-fukushima-plus-jamais-ca.jpg"/>
												</div>
											</li>
										</ul>
									</div>
								</div>
								<div class="span6">
									<div class="login-area">
										<div class="login-box" id="loginDiv">
											<div class="">
												<h3 class="login-header">Connexion &agrave; La Matrice</h3>
											</div>
											<form class="form-horizontal login-form" style="margin:0;" action="index.php?module=Users&action=Login" method="POST">
												{if isset($smarty.request.error)}
													<div class="alert alert-error">
														<p>Nom ou mot de passe incorrect.</p>
													</div>
												{/if}
												{if isset($smarty.request.fpError)}
													<div class="alert alert-error">
														<p>Nom ou adresse email incorrect.</p>
													</div>
												{/if}
												{if isset($smarty.request.status)}
													<div class="alert alert-success">
														<p>Un message vous a &eacute;t&eacute; envoy&eacute;, veuillez consulter votre bo&icirc;te mails.</p>
													</div>
												{/if}
												{if isset($smarty.request.statusError)}
													<div class="alert alert-error">
														<p>La configuration SMTP du serveur de mails est insuffisante.</p>
													</div>
												{/if}
												<div class="control-group">
													<label class="control-label" for="username"><b>Utilisateur</b></label>
													<div class="controls">
														<input type="text" id="username" name="username" placeholder="Utilisateur">
													</div>
												</div>

												<div class="control-group">
													<label class="control-label" for="password"><b>Mot de passe</b></label>
													<div class="controls">
														<input type="password" id="password" name="password" placeholder="Mot de passe">
													</div>
												</div>
												<div class="control-group signin-button">
													<div class="controls" id="forgotPassword">
														<button type="submit" class="btn btn-primary sbutton">Au boulot !</button>
														&nbsp;&nbsp;&nbsp;
														<br/><br/><small><a>j'ai encore oubli&eacute; mon mot de passe...</a></small>
													</div>
												</div>
												{* Retain this tracker to help us get usage details *}
												{*<img src='//stats.vtiger.com/stats.php?uid={$APPUNIQUEKEY}&v={$CURRENT_VERSION}&type=U' alt='' title='' border=0 width='1px' height='1px'>*}
											</form>
											<div class="login-subscript">
												<small> Bas&eacute; sur vtiger CRM {$CURRENT_VERSION}</small>
											</div>
										</div>
										
										<div class="login-box hide" id="forgotPasswordDiv">
											<form class="form-horizontal login-form" style="margin:0;" action="forgotPassword.php" method="POST">
												<div class="">
													<h3 class="login-header">Mot de passe perdu</h3>
												</div>
												<div class="control-group">
													<label class="control-label" for="username"><b>Utilisateur</b></label>
													<div class="controls">
														<input type="text" id="username" name="user_name" placeholder="Utilisateur">
													</div>
												</div>
												<div class="control-group">
													<label class="control-label" for="email"><b>Email</b></label>
													<div class="controls">
														<input type="text" id="email" name="emailId"  placeholder="Email">
													</div>
												</div>
												<div class="control-group signin-button">
													<div class="controls" id="backButton">
														<input type="submit" class="btn btn-primary sbutton" value="Valider" name="retrievePassword">
														&nbsp;&nbsp;&nbsp;<a>Retour</a>
													</div>
												</div>
											</form>
										</div>
										
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="navbar navbar-fixed-bottom">
			<div class="navbar-inner">
				<div class="container-fluid">
					<div class="row-fluid">
						<div class="span6 pull-left" >
							<div class="footer-content">
								<small>&#169 2004-{date('Y')}&nbsp;
									<a href="https://www.vtiger.com"> vtiger.com</a> | 
									<a href="javascript:mypopup();">License</a> </small>
							</div>
						</div>
						<div class="span6 pull-right" >
							<div class="pull-right footer-icons">
							</div>
						</div>
					</div>   
				</div>    
			</div>   
		</div>
	</body>
	<script>
		jQuery(document).ready(function(){
			jQuery("#forgotPassword a").click(function() {
				jQuery("#loginDiv").hide();
				jQuery("#forgotPasswordDiv").show();
			});
			
			jQuery("#backButton a").click(function() {
				jQuery("#loginDiv").show();
				jQuery("#forgotPasswordDiv").hide();
			});
			
			jQuery("input[name='retrievePassword']").click(function (){
				var username = jQuery('#user_name').val();
				var email = jQuery('#emailId').val();
				
				var email1 = email.replace(/^\s+/,'').replace(/\s+$/,'');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
				var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;
				
				if(username == ''){
					alert('Merci de saisir un nom d\'utilisateur valide');
					return false;
				} else if(!emailFilter.test(email1) || email == ''){
					alert('Merci de saisir une adresse email valide');
					return false;
				} else if(email.match(illegalChars)){
					alert( "L'adresse email contient des caract&egrave;res interdits.");
					return false;
				} else {
					return true;
				}
				
			});
		});
	</script>
</html>	
{/strip}
