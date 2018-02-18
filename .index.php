<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./.favicon.ico">
    <title>Directory Contents</title>

    <link rel="stylesheet" href="./.index.css">
    <link id='flexible' rel="stylesheet" href="./.table.css">

    <script src="./.sorttable.js"></script>
    <script src="./jquery2.2.4.min.js"></script>
    <script type='text/javascript'>
      $('document').ready(function(){
        console.log('session', sessionStorage.getItem('theme'));
        switch(sessionStorage.getItem('theme')) {
          case 'grid': {
            $('link[id="flexible"]').attr('href', './.grid.css');
            $('#display-type').attr('src', '.images/table-menu.svg');
            break;
          }
          default: {
            $('link[id="flexible"]').attr('href', './.table.css');
            $('#display-type').attr('src', '.images/grid-menu.svg');
          }
        }
        $('#display-type').click(function(){
          var style = $('#display-type').attr('src');
          if(style == '.images/grid-menu.svg'){
            sessionStorage.setItem('theme', 'grid');
            $('link[id="flexible"]').attr('href', './.grid.css');
            $('#display-type').attr('src', '.images/table-menu.svg');
          } else if ( style == '.images/table-menu.svg') {
            sessionStorage.setItem('theme', 'table');
            $('link[id="flexible"]').attr('href', './.table.css');
            $('#display-type').attr('src', '.images/grid-menu.svg');
          }
        });
      });
    </script>
  </head>
  <body>
    <div id='loader'></div>
    <div id="container">
    	<h1>Directory Contents</h1>
    	<table class="sortable">
        <thead>
          <tr>
            <th>Filename</th>
            <th>Type</th>
            <th>Size</th>
            <th>Date Modified</th>
            <th>Hidden</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Adds pretty filesizes
            function sizeInWords($size){
              if($size<1024){$size=$size." B";}
              elseif(($size<1048576)&&($size>1023)){$size=round($size/1024, 1)." KB";}
              elseif(($size<1073741824)&&($size>1048575)){$size=round($size/1048576, 1)." MB";}
              else{$size=round($size/1073741824, 1)." GB";}
              return $size;
            }
            function pretty_filesize($file) {
              $size=filesize($file);
              return sizeInWords($size);
            }
            function folderSize ($dir) {
              $size = 0;
              foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
                $size += is_file($each) ? filesize($each) : folderSize($each);
              }
              return $size;
            }

            // Checks to see if veiwing hidden files is enabled
            if($_SERVER['QUERY_STRING']=="hidden"){
              $hide="";
              $ahref="./";
              $atext="Hide";
            } else {
              $hide=".";
              $ahref="./?hidden";
              $atext="Show";
            }

            // Opens directory
            $myDirectory=opendir(".");

            // Gets each entry
            while($entryName=readdir($myDirectory)) {
              $dirArray[]=$entryName;
            }

            // Closes directory
            closedir($myDirectory);

            // Counts elements in array
            $indexCount=count($dirArray);

            // Sorts files
            sort($dirArray);

            // Loops through the array of files
            for($index=0; $index < $indexCount; $index++) {
              // Decides if hidden files should be displayed, based on query above.
              if(substr("$dirArray[$index]", 0, 1)!=$hide) {
                // Resets Variables
                $favicon="";
                $class="file";

                // Gets File Names
                $name=$dirArray[$index];
                $namehref=$dirArray[$index];

                // Gets Date Modified
                $modtime=date("M j Y g:i A", filemtime($dirArray[$index]));
                $timekey=date("YmdHis", filemtime($dirArray[$index]));


                // Separates directories, and performs operations on those directories
                if(is_dir($dirArray[$index])) {
                  $extn="&lt;Directory&gt;";
                  $size = sizeInWords(folderSize($dirArray[$index]));
                  $sizekey="0";
                  $class="dir";

                  // Gets favicon.ico, and displays it, only if it exists.
                  if(file_exists("$namehref/favicon.ico")) {
                  	$favicon=" style='background-image:url($namehref/favicon.ico);'";
                  	$extn="&lt;Website&gt;";
                  }

                  // Cleans up . and .. directories
                  if($name=="."){$name="(Current Directory)"; $extn="&lt;System Dir&gt;"; $favicon=" style='background-image:url($namehref/.favicon.ico);'";}
                  if($name==".."){$name="(Parent Directory)"; $extn="&lt;System Dir&gt;";}
                }

                // File-only operations
                else {
                  // Gets file extension
                  $extn=pathinfo($dirArray[$index], PATHINFO_EXTENSION);

                  // Prettifies file type
                  switch ($extn){
                    case "png": $extn="PNG Image"; break;
                    case "jpg": $extn="JPEG Image"; break;
                    case "jpeg": $extn="JPEG Image"; break;
                    case "svg": $extn="SVG Image"; break;
                    case "gif": $extn="GIF Image"; break;
                    case "ico": $extn="Windows Icon"; break;

                    case "txt": $extn="Text File"; break;
                    case "log": $extn="Log File"; break;
                    case "htm": $extn="HTML File"; break;
                    case "html": $extn="HTML File"; break;
                    case "xhtml": $extn="HTML File"; break;
                    case "shtml": $extn="HTML File"; break;
                    case "php": $extn="PHP Script"; break;
                    case "js": $extn="Javascript File"; break;
                    case "css": $extn="Stylesheet"; break;

                    case "pdf": $extn="PDF Document"; break;
                    case "xls": $extn="Spreadsheet"; break;
                    case "xlsx": $extn="Spreadsheet"; break;
                    case "doc": $extn="Microsoft Word Document"; break;
                    case "docx": $extn="Microsoft Word Document"; break;

                    case "zip": $extn="ZIP Archive"; break;
                    case "htaccess": $extn="Apache Config File"; break;
                    case "exe": $extn="Windows Executable"; break;

                    default: if($extn!=""){$extn=strtoupper($extn)." File";} else{$extn="Unknown";} break;
                  }

                  // Gets and cleans up file size
                  $size=pretty_filesize($dirArray[$index]);
                  $sizekey=filesize($dirArray[$index]);
                }
                $hidden = '';
                if(strpos($name, ".") === 0){
                  $name = explode(".", $name)[1];
                  $hidden = "<a href='./$namehref'>Hidden</a>";
                } else if(strpos($name, ".") !== true && strpos($name, ".") === false){
                  $name = $name;
                } else {
                  $name = explode(".", $name)[0];
                }
                echo("
                <tr class='$class'>
                  <td id='name'><a href='./$namehref'$favicon class='name'>".$name."</a></td>
                  <td id='type'><a href='./$namehref'>$extn</a></td>
                  <td id='size' sorttable_customkey='$sizekey'><a href='./$namehref'>$size</a></td>
                  <td id='timeStamp' sorttable_customkey='$timekey'><a href='./$namehref'>$modtime</a></td>
                  <td id='hidden' sorttable_customkey='$hiddenKey'>$hidden</td>
                </tr>");
              }
            }
          ?>
        </tbody>
      </table>
    	<?php echo("<a href='$ahref'><h2 class='hiddenFiles'>$atext hidden files</h2></a>"); ?>
      <div style='position: fixed; right: 0px; bottom:0px; padding: 5px; box-shadow: 0 0 10px 0px #ddd;'>
        <img src='.images/grid-menu.svg' id='display-type' />
      </div>
    </div>
  </body>
</html>
