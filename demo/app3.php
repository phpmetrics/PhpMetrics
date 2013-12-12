<?php
if(!defined('entry') || !entry) die('Not a valid page');
/* ===========================

  gelato CMS - A PHP based tumblelog CMS
  development version
  http://www.gelatocms.com/

  gelato CMS is a free software licensed under the GPL 2.0
  Copyright (C) 2007 by Pedro Santana <pecesama at gmail dot com>

  =========================== */

class gelato {
    var $db;
    var $conf;

    function gelato() {
        global $db;
        global $conf;

        $this->db = $db;
        $this->conf = $conf;
    }

    function saveSettings($fieldsArray) {
        if ($this->db->modificarDeFormulario($this->conf->tablePrefix."config", $fieldsArray)) {
            header("Location: ".$this->conf->urlGelato."/admin/settings.php?modified=true");
            die();
        } else {
            header("Location: ".$this->conf->urlGelato."/admin/settings.php?error=1&des=".$this->db->merror);
            die();
        }
    }

    function saveOption($value, $name) {
        $sqlStr = "UPDATE ".$this->conf->tablePrefix."options SET val='".$value."' WHERE name='".$name."' LIMIT 1";
        if ($this->db->ejecutarConsulta($sqlStr)) {
            return true;
        } else {
            return true;
        }
    }

    function addPost($fieldsArray) {
        if ($this->db->insertarDeFormulario($this->conf->tablePrefix."data", $fieldsArray)) {
            return true;
        } else {
            return false;
        }
    }

    function modifyPost($fieldsArray, $id_post) {
        if ($this->db->modificarDeFormulario($this->conf->tablePrefix."data", $fieldsArray, "id_post=$id_post")) {
            header("Location: ".$this->conf->urlGelato."/admin/index.php?modified=true");
            die();
        } else {
            header("Location: ".$this->conf->urlGelato."/admin/index.php?error=2&des=".$this->db->merror);
            die();
        }
    }

    function deletePost($idPost) {
        $this->db->ejecutarConsulta("DELETE FROM ".$this->conf->tablePrefix."data WHERE id_post=".$idPost);
    }

    function getPosts($limit="10", $from="0") {
        $sqlStr = "select * from ".$this->conf->tablePrefix."data ORDER BY date DESC LIMIT $from,$limit";
        $this->db->ejecutarConsulta($sqlStr);
        return $this->db->mid_consulta;
    }

    function getPost($id="") {
        $this->db->ejecutarConsulta("select * from ".$this->conf->tablePrefix."data WHERE id_post=".$id);
        return mysql_fetch_array($this->db->mid_consulta);
    }

    function getPostsNumber() {
        $this->db->ejecutarConsulta("select count(*) as total from ".$this->conf->tablePrefix."data");
        $row = mysql_fetch_assoc($this->db->mid_consulta);
        return $row['total'];
    }

    function getType($id) {
        if ($this->db->ejecutarConsulta("select type from ".$this->conf->tablePrefix."data WHERE id_post=".$id)) {
            if ($this->db->contarRegistros()>0) {
                while($registro = mysql_fetch_array($this->db->mid_consulta)) {
                    return $registro[0];
                }
            }
        } else {
            return "0";
        }
    }

    function formatConversation($text) {
        $formatedText = "";
        $odd=true;

        $lines = explode("\n", $text);

        $formatedText .= "<ul>\n";
        foreach ($lines as $line) {
            $pos = strpos($line, ":") + 1;

            $label = substr($line, 0, $pos);
            $desc = substr($line, $pos, strlen($line));

            if ($odd) {
                $cssClass = "odd";
            } else {
                $cssClass = "even";
            }
            $odd=!$odd;


            $formatedText .= "      <li class=\"".$cssClass."\">\n";
            $formatedText .= "              <span class=\"label\">".$label."</span>\n";
            $formatedText .= "              ".$desc."\n";
            $formatedText .= "      </li>\n";
        }
        $formatedText .= "</ul>\n";
        return $formatedText;
    }

    function formatApiConversation($text) {
        $formatedText = "";

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $pos = strpos($line, ":") + 1;

            $name = substr($line, 0, $pos-1);
            $label = substr($line, 0, $pos);
            $desc = substr($line, $pos, strlen($line));

            $formatedText .= "<conversation-line name=\"".$name."\" label=\"".$label."\">".$desc."</conversation-line>\n";
        }

        return $formatedText;
    }

    function saveMP3($remoteFileName) {
        if (util::getMP3File($remoteFileName)) {
            return true;
        } else {
            return false;
        }
    }

    function savePhoto($remoteFileName) {
        if (util::getPhotoFile($remoteFileName)) {
            return true;
        } else {
            return false;
        }
    }

    function getVideoPlayer($url) {
        if (util::isYoutubeVideo($url)) {
            $id_video = util::getYoutubeVideoUrl($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://www.youtube.com/v/".$id_video."\"><param name=\"movie\" value=\"http://www.youtube.com/v/".$id_video."\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isVimeoVideo($url)) {
            $id_video = util::getVimeoVideoUrl($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://www.vimeo.com/moogaloop.swf?clip_id=".$id_video."\"><param name=\"movie\" value=\"http://www.vimeo.com/moogaloop.swf?clip_id=".$id_video."\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isDailymotionVideo($url)) {
            $id_video = util::getDailymotionVideoUrl($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://www.dailymotion.com/swf/".$id_video."\"><param name=\"movie\" value=\"http://www.dailymotion.com/swf/".$id_video."\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isYahooVideo($url)) {
            $id_video = util::getYahooVideoCode($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf\"><param name=\"quality\" value=\"high\" /><param name=\"FlashVars\" value=\"event_function=YAHOO.yv.Player.SWFInterface&amp;id=".$id_video[1]."&amp;vid=".$id_video[0]."&amp;onsite=1&amp;site=video.yahoo.com&amp;page=792730258&amp;lang=en-US&amp;intl=us\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isSlideSharePresentation($url)) {
            $id_video = util::getSlideSharePresentationCode($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://www.slideshare.net/swf/player.swf?presentationId=".$id_video[0]."&amp;doc=".$id_video[1]."&amp;inContest=0&amp;startSlide=1\"><param name=\"quality\" value=\"high\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isGoogleVideoUrl($url)) {
            $id_video = util::getGoogleVideoCode($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://video.google.com/googleplayer.swf?docid=".$id_video."&amp;hl=es&amp;fs=true\"><param name=\"movie\" value=\"http://video.google.com/googleplayer.swf?docid=".$id_video."&amp;hl=es&amp;fs=true\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isMTVVideoUrl($url)) {
            $id_video = util::getMTVVideoCode($url);
            return "\t\t\t<object type=\"application/x-shockwave-flash\" style=\"width:500px;height:393px\" data=\"http://media.mtvnservices.com/mgid:uma:video:mtvmusic.com:".$id_video."\"><param name=\"movie\" value=\"http://media.mtvnservices.com/mgid:uma:video:mtvmusic.com:".$id_video."\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"never\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } else {
            return "This URL is not a supported video (YouTube, Google Video, Vimeo, DailyMotion, Yahoo Video, MTV or SlideShare)";
        }
    }
    function getMp3Player($url) {
        if (util::isMP3($url)) {
            $playerUrl = $conf->urlGelato."/admin/scripts/player.swf?soundFile=".$url;
            return "\t\t\t<object type=\"application/x-shockwave-flash\" data=\"" . $playerUrl . "\" width=\"290\" height=\"24\"><param name=\"movie\" value=\"" . $playerUrl . "\" /><param name=\"quality\" value=\"high\" /><param name=\"menu\" value=\"false\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isGoEar($url)) {
            return "\t\t\t<object type=\"application/x-shockwave-flash\" data=\"http://www.goear.com/files/external.swf\" width=\"366\" height=\"130\"><param name=\"movie\" value=\"http://www.goear.com/files/external.swf\" /><param name=\"quality\" value=\"high\" /><param name=\"FlashVars\" value=\"file=".util::getGoEarCode($url)."\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } elseif (util::isOdeo($url)) {
            return "\t\t\t<object type=\"application/x-shockwave-flash\" data=\"http://media.odeo.com/flash/odeo_player.swf?v=3\" width=\"366\" height=\"75\"><param name=\"quality\" value=\"high\" /><param name=\"FlashVars\" value=\"type=audio&amp;id=".util::getOdeoCode($url)."\" /><param name=\"wmode\" value=\"transparent\" /></object>\n";
        } else {
            return "This URL is not an MP3 file.";
        }
    }

    function getPermalink($post_id){
        $strEnd = ($this->conf->urlFriendly) ? "/" : "";
        $out = $this->conf->urlGelato;
        $out .= ($this->conf->urlFriendly) ? "/post/" : "/index.php?post=";
        $out .= $post_id.$strEnd;
        return $out;
    }
}
?>