<?php
function downfile($fileurl)
{
 ob_start(); 
 $filename=$fileurl;
 $date="chstask-".date("YmdHim");
 header( "Content-type:  application/octet-stream "); 
 header( "Accept-Ranges:  bytes "); 
 header( "Content-Disposition:  attachment;  filename= {$date}.apk"); 
 $size=readfile($filename); 
  header( "Accept-Length: " .$size);
}
 $url="chstask.apk";
 downfile($url);
?>