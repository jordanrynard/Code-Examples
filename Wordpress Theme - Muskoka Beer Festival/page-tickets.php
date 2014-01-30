<?php
/**
 * The template for displaying all pages.
 *
 */

get_header(); ?>

	<div id="primary">
		<div id="content" role="main" style="min-height:925px;">
			<? while ( have_posts() ) : the_post(); ?>

				<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
					<div id="success" style="position:absolute;margin-top:-100px;"></div>
					<h1 class="entry-title" style="margin-top:10px">Tickets!!</h1>
				
					<div class="entry-content">
						<div class="right">

							<? if (isset($_GET['purchase']) && $_GET['purchase']==1): ?>
								<div class="success">
									Thank you for your purchase!<br/>
									<div style="font-size:18px; line-height:normal">
										Any tickets purchased after Friday June 9th will be held at WILL CALL to be picked up - please bring valid I.D.<br/>
										<br/>
									</div>
									See you there!

									<?php
										$pp_hostname = "www.paypal.com"; // Change to www.sandbox.paypal.com to test against sandbox
										 
										// read the post from PayPal system and add 'cmd'
										$req = 'cmd=_notify-synch';
										 
										$tx_token = $_GET['tx'];
										$auth_token = "dwRP7D3-owvk6skeko3mPMuYuKThGu7S6apWONH7IAFIxCSd_L9gonA8YjK";
										$req .= "&tx=$tx_token&at=$auth_token";

										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
										curl_setopt($ch, CURLOPT_POST, 1);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
										curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
										curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
										$res = curl_exec($ch);
										curl_close($ch);
										 
										if(!$res){
										    //HTTP ERROR
											echo "There was a problem"; 
										}else{
										     // parse the data
										    $lines = explode("\n", $res);
										    $keyarray = array();
										    if (strcmp ($lines[0], "SUCCESS") == 0) {
										        for ($i=1; $i<count($lines);$i++){
											        list($key,$val) = explode("=", $lines[$i]);
											        $keyarray[urldecode($key)] = urldecode($val);
										    	}
											    update_option('purchase_'.time(),json_encode($keyarray));
											} else if (strcmp ($lines[0], "FAIL") == 0) {
										        // log for manual investigation
											}
										}
									?>
								</div>

							<? else: ?>

								<form id="payment_form_for_paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
									<div class="text">
										Online ticket sales are now closed.<br/><br/>
										Tickets will be available for purchase at the gate.<br/>						
										<br/>
									</div>									
								</form>

							<? endif; ?>
					
						</div>

						<div class="left">
							<div class="ticket-details">
								<div class="details">
									$25 / person<br/>
									includes entrance,<br/>
									a sampling glass,<br/>
									wicked entertainment<br/>
									and 5 tasting tokens<br/>
									<br/>
									<div class="additional">*Food can be purchased <br/>at the festival.</div>
								</div>
							</div>
						</div>

						<div class="bottom">	
							<div class="details">
								Group rates available (10+ people)<br/>
								Please contact us at: <br/>
								<b><i>craftbeerevents@gmail.com</i></b>
							</div>
							<div class="seperator"></div>
							<div class="keep-in-mind">
								Please keep in mind this is 19+ event and you will definitely need photo I.D. to enter and pets are NOT allowed.
							</div>
						</div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>