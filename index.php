<?php echo('<?xml version="1.0" encoding="UTF-8"?>'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php

/*

Copyright 2009 Magnus Enger (magnus@enger.priv.no)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

include_once('config.php');
require_once($phpflickr);
$f = new phpFlickr($api_key);

if ($enable_cache) {
  $f->enableCache(
    "db",
    $db_conn
  );
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Norsk Lundehund Klubb - Galleri</title>
<link rel="stylesheet" type="text/css" href="default.css" />
</head>
<body>
<div id="main">

<?php

if (empty($_GET['clean'])) {

?>

<h1>Norsk Lundehund Klubb - Galleri</h1>
<p class="menu">
<a href="?">Galleriets forside</a> <?php echo($link_sep) ?>
<a href="?v=browse">De nyeste bildene</a> <?php echo($link_sep) ?>
<a href="?v=members">Bidragsytere</a> <?php echo($link_sep) ?>
<a href="/galleri/del-dine-bilder">Del dine bilder!</a> <?php echo($link_sep) ?> 
<a href="/">Til NLKs hjemmeside</a> <?php echo($link_sep) ?>
<a href="/galleri">Til galleriet p&aring; NLKs hjemmeside</a>
</p>

<?php

}

?>

<?php

$pics_per_page = $pics_per_row * $rows_per_page;


if (!empty($_GET['id']) && is_numeric($_GET['id'])) {

  // DISPLAY ONE PHOTO

  $photo = $f->photos_getInfo($_GET['id']);
  $owner = $photo['owner']['username'];
	
  // $context = $f->photos_getContext($_GET['id']);
	
	$pool_context = $f->groups_pools_getContext($_GET['id'], $groupid);
	// DEBUG print_r($pool_context);
  
  echo "\n<h2>". clean_norwegian_string($photo['title']) ."</h2>\n";
	echo("<p>");
	if ($pool_context['nextphoto']['id'] != 0) {
	  echo("<a href=\"?id={$pool_context['nextphoto']['id']}\"><img alt=\"Forrige bilde\" title=\"Forrige bilde\" src=" . $f->buildPhotoURL($pool_context['nextphoto'], "Square") . "></a> ");
	}
  echo "<img alt=\"". clean_norwegian_string($photo['title']) ."\" src=\"".$f->buildPhotoUrl($photo, $size)."\" />\n";
	if ($pool_context['prevphoto']['id'] != 0) {
	  echo("<a href=\"?id={$pool_context['prevphoto']['id']}\"><img alt=\"Neste bilde\" title=\"Neste bilde\" src=" . $f->buildPhotoURL($pool_context['prevphoto'], "Square") . "></a> ");
	}
	echo("</p>");
	if ($photo['description']) {
    echo "<p>". clean_norwegian_string($photo['description']) ."</p>\n";
	}
	
	// TODO Extract info about set if this was published by $userid
	
  echo "<p>Bildet er lagt ut av <a href=\"http://www.flickr.com/photos/".$photo['owner']['nsid']."\">". clean_norwegian_string($owner) ."</a>.\n";
  echo "<a href=\"http://www.flickr.com/photos/{$photo['owner']['nsid']}/{$photo['id']}\">Se originalbildet p&aring; Flickr</a>.</p>\n";

} elseif (!empty($_GET['v']) && $_GET['v'] == 'members') {

  // MEMBERS

	echo("<h2>Bidragsytere</h2>\n<table width=\"100%\"><tr><td width=\"50%\" valign=\"top\">");
	
	// Contacts
	
  $contacts = $f->contacts_getPublicList($userid);
  echo("<p>Bidragsytere som selv legger inn bilder i galleriet:</p>
				<table border=\"0\" align=\"center\">");
  foreach($contacts['contact'] as $c) {
	  echo("<tr><td>");
		$link = "?member={$c['nsid']}";
	  if ($c['iconserver']) {
      echo("<a href=\"$link\"><img src=\"http://farm{$c['iconfarm']}.static.flickr.com/{$c['iconserver']}/buddyicons/{$c['nsid']}.jpg\" alt=\"icon\"></a>");
		} else {
		  echo("<a href=\"$link\"><img src=\"http://l.yimg.com/g/images/buddyicon.jpg\" alt=\"icon\"></a>");
		}
		echo("</td>");
		echo("<td><h3><a href=\"$link\">" . clean_norwegian_string($c['username']) . "</a></h3></td>");
  	// echo("<td><a href=\"http://www.flickr.com/photos/{$c['nsid']}/\">Alle bilder</a><br /><a href=\"http://www.flickr.com/photos/{$c['nsid']}/tags/lundehund\">Bilder tagget med &quot;lundehund&quot;</a></td>");
  }
	echo("</tr></table>");
	
	echo("</td><td width=\"50%\" valign=\"top\">");
	
	// Sets
	
	$sets = $f->photosets_getList($userid);
	echo("<p>Bidragsytere som legger bilder i galleriet via klubben:</p>
	      <table>");
  foreach ($sets['photoset'] as $set){
	  // debug_var($set);
    // $set = $f->photosets_getInfo($set);
    $primary = $f->photos_getInfo($set['primary']);
		$link = "?member={$set['id']}";
    echo "<tr><td><a href=\"$link\"><img src=\"" . $f->buildPhotoURL($primary, "square") . "\" height=\"48\" width=\"48\" alt=\"". clean_norwegian_string($set['title']) ."\" /></a></td>";
    echo "<td><h3><a href=\"$link\">" . clean_norwegian_string($set['title']) . " <!-- (" . $set['photos'] ." bilder) --></a></h3></td>";
    // echo "<p>". clean_norwegian_string($set['description']) ."</p>";
  }
	echo("</table>");
	
	echo("</td></tr></table>");
	
  echo("<p>Dersom du bidrar med bilder til galleriet, men <em>ikke</em> &oslash;nsker &aring; st&aring; p&aring; denne lista: ta kontakt med vevsjefen!</p>");

} elseif (!empty($_GET['member'])) {

  $photos = "";
	$title = "";
	
	$page = 1;
  if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
  	  $page = $_GET['page'];
  }
	
	if (substr_count($_GET['member'], '@') > 0) {
    // Get the photos from this member, in this group
    $photos = $f->groups_pools_getPhotos($groupid, NULL, $_GET['member'], NULL, $pics_per_page, $page);
	  $memberinfo = $f->people_getInfo($_GET['member']);
		$title = $memberinfo['username'];
	} else {
	  // Get the photos from this set
		$photos  = $f->photosets_getPhotos($_GET['member'], NULL, NULL, $pics_per_page, $page);
		$setinfo = $f->photosets_getInfo($_GET['member']);
		$title = $setinfo['title'];
	}
	
	echo("<h2>Bilder fra $title</h2>");
  display_photos($photos, $page);

} elseif (!empty($_GET['v']) && $_GET['v'] == 'browse') {

  $page = 1;
	if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
	  $page = $_GET['page'];
	}
  $photos = $f->groups_pools_getPhotos($groupid, NULL, NULL, NULL, $pics_per_page, $page);
  display_photos($photos, $page);

} else {

  // DEFAULT

  $photos = $f->groups_pools_getPhotos($groupid, NULL, NULL, NULL, 150, $page);
	$lastowner = "";
	$c = 0;
	echo("<table align=\"center\">");
  foreach ((array)$photos['photo'] as $photo) {
	  if ($photo['owner'] != $lastowner) {
		  // Wrap up last member
			if ($c > 0) {
			  if ($c > $pics_per_member) {
	        $not_displayed = $c - $pics_per_member;
		      echo(" og <a href=\"?member=$lastowner\" target=\"_top\">$not_displayed bilde(r) til</a>.");
	      }
				echo("</p></td></tr>");
			}
		  // We have a new member
		  echo("<tr><td><p style=\"text-align: right;\"><a href=\"?member={$photo['owner']}\" target=\"_top\">" . $photo['ownername'] . "</a>");
			if ($photo['owner'] == $userid) {
			  echo("<br />p&aring; vegne av bidragsyter(e)");
			}
			echo(":</p></td><td valign=\"center\"><p style=\"text-align: left;\">\n");
			$lastowner = $photo['owner'];
			$c = 0;
		} 
		if ($c < $pics_per_member) {
		  echo "<a href=\"?id=" . $photo['id'] . $narrowlink . "\" target=\"_top\">";
      echo "<img alt='$photo[title]' title='$photo[title]' src=" . $f->buildPhotoURL($photo, "Square") . ">";
      echo "</a> \n";
		}
		$c++;
  }
	if ($c > $pics_per_member) {
	  $not_displayed = $c - $pics_per_member;
		echo(" og <a href=\"?member=$lastowner\" target=\"_top\">$not_displayed bilde(r) til</a>.");
	} 
	echo("</p></td></tr>");
	echo("</table>");

}

function display_photos($photos, $page) {

  global $f, $pics_per_page, $pics_per_row;

  if (count($photos['photo']) == 0) {
    echo("<p style=\"font-weight: bold; font-size: 200%; margin-top: 7em;\">Vi beklager, teknisk feil!</p>");
  	echo("<p>Inntil feilen er rettet opp kan du se bildene <a target=\"_top\" href=\"http://flickr.com/groups/lundehund/pool/\">hos Flickr</a>.</p>");
		echo("<p>Error code: " . $f->getErrorCode() . "</p>");
		echo("<p>Error message: " . $f->getErrorMsg() . "</p>");
    echo("<!--");
    print_r($photos);
    echo("-->");
    // exit;
  }
	
	if (empty($_GET['clean'])) {
    // Count the photos in the response
    $count = count($photos['photo']);
  	$pcounter = 0;
    // from x to y of z photos
    $x = ($page * $pics_per_page) - $pics_per_page + 1;
    $y = $x + $count - 1;
    $total_pages = round($photos['total'] / $pics_per_page);
  	if ($total_pages == 0) {
  	  $total_pages = 1;
  	}
    echo("<p>Side $page av $total_pages, bilde $x - $y av {$photos['total']} ");
  	// Should we narrow by user or set? 
    $narrowlink = "";
  	if (!empty($_GET['member'])) {
  	  $narrowlink = "&member={$_GET['member']}";
  	} 
  	// Next and previous links
    if ($page > 1){
    	$previous = $page - 1;
    	echo "[<a href=\"?v={$_GET['v']}&page=$previous{$narrowlink}\">Forrige side</a>]\n";
    } else {
      echo("[Forrige side]");
    }
    if ($page*$pics_per_page < $photos['total']) {
      $next = $page + 1;
      echo "[<a href=\"?v={$_GET['v']}&page=$next{$narrowlink}\">Neste side</a>]\n";
    } else {
      echo("[Neste side]");
    }
    echo "</p>\n";
	}

  foreach ((array)$photos['photo'] as $photo) {
      // echo "<a href=http://www.flickr.com/photos/" . $photo['owner'] . "/" . $photo['id'] .">";
			echo "<a href=\"?id=" . $photo['id'] . $narrowlink . "\" target=\"_top\">";
      echo "<img alt='$photo[title]' title='$photo[title]' src=" . $f->buildPhotoURL($photo, "Square") . ">";
      echo "</a> ";
  		$pcounter++;
  		if ($pcounter % $pics_per_row == 0) {
  		  echo("<br />");
  		}
  }

}

function clean_norwegian_string($s) {
  /*
	$s = str_replace('Ã¸', 'ø', $s);
	$s = str_replace('Ã…', 'Å', $s);
  $s = str_replace('Ã¥', 'å', $s);
	$s = str_replace('Ã¦', 'æ', $s);
	$s = str_replace('Ã¤', 'ä', $s);
	$s = str_replace('Ã„', 'Ä', $s);
	$s = str_replace('Ã¶', 'ö', $s);
	$s = str_replace('â€œ', '&quot;', $s);
	$s = str_replace('â€', '&quot;', $s);
	*/
	return $s;
}

function debug_var($v) {
  echo("<pre>");
  print_r($v);
  echo("</pre>");
}

?>
</div>

</body>
</html>
