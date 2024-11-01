<?php

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'getting_started';

$title = sprintf( __( 'Welcome to Ultimate Private Member Portal %s', 'upmp' ), UPMP_VERSION ) ;
$desc = __( 'Thank you for choosing Ultimate Private Member Portal.','upmp');

?>

<div class="wrap about-wrap">
	<h1><?php echo $title; ?></h1>
	<div class="about-text">
		<?php echo $desc; ?>
		
	</div>
	<h2>Documentation Coming Soon</h2>
</div>
