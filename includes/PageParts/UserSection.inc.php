<?php

/**

 * Get either a Gravatar URL or complete image tag for a specified email address.

 *

 * @param string $email The email address

 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]

 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]

 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]

 * @param boole $img True to return a complete IMG tag False for just the URL

 * @param array $atts Optional, additional key/value attributes to include in the IMG tag

 * @return String containing either just a URL or a complete image tag

 * @source http://gravatar.com/site/implement/images/php/

 */

function get_gravatar( $email, $s = 30, $d = 'mm', $r = 'g', $img = false, $atts = array() ) 

{

	$url = '//www.gravatar.com/avatar/';

	$url .= md5( strtolower( trim( $email ) ) );

	$url .= "?s=$s&d=$d&r=$r";

	

	if ( $img )

	{

		$url = '<img src="' . $url . '"';

		foreach ( $atts as $key => $val )

		    $url .= ' ' . $key . '="' . $val . '"';

		    $url .= ' />';	

	}

	return $url;

}
?>
						

<li class="dropdown current-user">

							<a data-toggle="dropdown" class="dropdown-toggle" href="#">

							

								<?php

								$atts = array('alt'=>'Grav');

								

								$Gravatar  = get_gravatar($oUser->EmailAddress, 30, 'mm', 'g', false, $atts);

								?>

															

								<img src="<?php print $Gravatar; ?>" class="circle-img" alt="">

								<span class="username"><?php print $oUser->FirstName." ".$oUser->Surname; ?></span>

								<i class="clip-chevron-down"></i>

							</a>

							<ul class="dropdown-menu">

								<?php
								if( $ClientID > 0 )
								{
								?>
								<li>

									<a href="/users/AddUser.php?UserID=<?php print $oUser->ClientID; ?>">

										<i class="clip-user-2"></i>

										&nbsp;My Profile

									</a>

								</li>
								<?php
								}
								?>
				

								<li>

									<a href="/logout.php">

										<i class="clip-exit"></i>

										&nbsp;Log Out

									</a>

								</li>

							</ul>

						</li>

						<!-- end: USER DROPDOWN -->
