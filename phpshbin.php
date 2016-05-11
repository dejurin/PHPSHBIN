<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();

$_rd = '1a1dc91c907325c69271ddf0c944bc72';

if (! isset($_SERVER['PHP_AUTH_USER']) && ! isset($_SERVER['PHP_AUTH_PW'])) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  exit;
}

if (md5($_SERVER['PHP_AUTH_USER']) == 'ee11cbb19052e40b07aac0ca060c23ee' && md5($_SERVER['PHP_AUTH_PW']) == $_rd) {

  $path = dirname(realpath(__FILE__));
  $script = basename(__FILE__);

  if (! array_key_exists('phpshbin_path', $_COOKIE)) {
    $_COOKIE['phpshbin_path'] = $path;
  }

  // Classes
  Class Tree_view {
    public function php_file_tree($directory, $return_link, $extensions = array()) {
      $code = null;
      if (substr($directory, -1) == '/') $directory = substr($directory, 0, strlen($directory) - 1);
      $code .= $this->php_file_tree_dir($directory, $return_link, $extensions);
      return $code;
    }

    private function php_file_tree_dir($directory, $return_link, $extensions = array(), $first_call = true) {
      if (function_exists("scandir")) $file = scandir($directory); else $file = $this->php4_scandir($directory);
      natcasesort($file);

      $files = $dirs = array();
      foreach ($file as $this_file) {
        if (is_dir($directory."/".$this_file)) $dirs[] = $this_file; else $files[] = $this_file;
      }
      $file = array_merge($dirs, $files);
      
      // Filter unwanted extensions
      if( !empty($extensions) ) {
        foreach( array_keys($file) as $key ) {
          if (! is_dir("$directory/$file[$key]")) {
            $ext = substr($file[$key], strrpos($file[$key], ".") + 1); 
            if (! in_array($ext, $extensions)) unset($file[$key]);
          }
        }
      }
     
      $_php_file_tree = null;
      if (count($file) > 1) { 
        // Use 2 instead of 0 to account for . and .. "directories"
        $_php_file_tree = '<ul';
        if ($first_call) {
          $_php_file_tree .= ' class="php-file-tree"'; $first_call = false; 
        }
        $_php_file_tree .= '>';
        $total_size = 0;
        foreach ($file as $this_file) {
          if ($this_file != "." && $this_file != ".." ) {
            
            if (is_dir($directory.'/'.$this_file)) {
              // Directory
              $this_file = htmlspecialchars($this_file);
              //$_this_file = str_replace(' ', '_', $this_file);
             // $_class = (isset($_COOKIE[$_this_file]) && $_COOKIE[$_this_file]) ? 'open':'closed';

              $_php_file_tree .= '<li class="pft-directory"><input type="checkbox" name="file[]"> <a href="#">'.$this_file.'</a>';
              $_php_file_tree .= $this->php_file_tree_dir($directory.'/'.$this_file, $return_link, $extensions, false);
              $_php_file_tree .= '</li>';
            } 
            else {
              // File
              // Get extension (prepend 'ext-' to prevent invalid classes from extensions that begin with numbers)
              $ext = "ext-" . substr($this_file, strrpos($this_file, ".") + 1);
              $link = str_replace("[link]", $directory.'/'.urlencode($this_file), $return_link);
              $total_size += filesize($directory.'/'.$this_file);
              $_php_file_tree .= '<li class="pft-file '.strtolower($ext).'"><input type="checkbox" name="file[]"> <a href="'.$link.'">' . htmlspecialchars($this_file) . '</a>';
            }
          }
        }

        $_php_file_tree .= '<li class="pft-command"><b>'.human_filesize($total_size).'</b></li>';
        $_php_file_tree .= '<li class="pft-command"><button onclick="newFile(\''.$directory.'\')">New file</button> <button onclick="newFolder(\''.$directory.'\')">New folder</button></li>';
        $_php_file_tree .= "</ul>";
      }
      return $_php_file_tree;
    }

    // For PHP4 compatibility
    private function php4_scandir($dir) {
      $dh  = opendir($dir);
      while( false !== ($filename = readdir($dh)) ) {
          $files[] = $filename;
      }
      sort($files);
      return($files);
    }
  }

  // Functions start

  function make_new_file($path, $ext = '.txt', $num = 0) {
    $name = 'new_file' . (($num > 0) ? $num:'');
    $full = $path.'/'.$name.$ext;
    if (file_exists($full)) {
      if ($num >= 10) return false;
      $num++;
      if (make_new_file($path, $ext, $num)) {
        return true;
      }
      else {
        return false;
      }
    }
    else {
      $fp = fopen($full, 'w');
      fclose($fp);
      if (file_exists($full)) {
        return true;
      }
      else {
        return false;
      }
    }
  }

  function make_new_folder($path, $num = 0) {
    $name = 'new_folder' . (($num > 0) ? $num:'');
    $full = $path.'/'.$name;
    if (is_dir($full)) {
      if ($num >= 10) return false;
      $num++;
      if (make_new_folder($path, $num)) {
        return true;
      }
      else {
        return false;
      }
    }
    else {
      if (mkdir($full, 0755, true)) {
        return true;
      }
      else {
        return false;
      }
    }
  }

  function human_filesize($size, $precision = 2) {
    $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $step = 1024;
    $i = 0;
    while (($size / $step) > 0.9) {
        $size = $size / $step;
        $i++;
    }
    return round($size, $precision).' '.$units[$i];
  }

  // Functions end

  echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>PHPSHBIN</title></head><body><h1>PHPSHBIN</h1><hr>PATH: <form method="get" action="'.$script.'"><input name="path" value="'.$_COOKIE['phpshbin_path'].'" style="width:280px;"><input type="submit" name="submit"></form><hr>';

  if (array_key_exists('open', $_GET)) {
    
  }
  else if (array_key_exists('path', $_GET)) {
    SetCookie('phpshbin_path', urldecode($_GET['path']), time() + 259200);
    header('Location: shell.php'); 
  }
  else if (array_key_exists('newfile', $_GET)) {
    if (make_new_file($_GET['newfile'])) {
      //header('HTTP/1.1 301 Moved Permanently'); 
      header('Location: shell.php');
    }
    else {
      echo "Error\n";
      echo '<button onclick="window.history.back();">Back</button>';
    }
  }
  else if (array_key_exists('newfolder', $_GET)) {
    if (make_new_folder($_GET['newfolder'])) {
      header('Location: shell.php');
    }
    else {
      echo "Error\n";
      echo '<button onclick="window.history.back();">Back</button>';
    }
  }
  else if (array_key_exists('uploadfile', $_GET)) {
    echo '<form action="" method="POST" enctype="multipart/form-data"><input type="file" name="image">'."\n".'<button onclick="window.history.back();">Cancel</button>'."\n".'<input type="submit" name="submit"></form>';
  }
  else if (array_key_exists('edit', $_GET)) {
    $file = $_GET['edit'];
    $file = file_get_contents($file);
    echo '<form><textarea style="width:100%;height:320px;" name="file">'.htmlspecialchars($file).'</textarea><button onclick="window.history.back();">Cancel</button><input type="submit" name="submit"></form>';
  }
  else {
    $tree = new Tree_view();
?>
<style>.php-file-tree A{color:#000;text-decoration:none}.php-file-tree A:hover{color:#666}.php-file-tree .open{font-style:italic}.php-file-tree .closed{font-style:normal}.php-file-tree .pft-directory{list-style-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGrSURBVDjLxZO7ihRBFIa/6u0ZW7GHBUV0UQQTZzd3QdhMQxOfwMRXEANBMNQX0MzAzFAwEzHwARbNFDdwEd31Mj3X7a6uOr9BtzNjYjKBJ6nicP7v3KqcJFaxhBVtZUAK8OHlld2st7Xl3DJPVONP+zEUV4HqL5UDYHr5xvuQAjgl/Qs7TzvOOVAjxjlC+ePSwe6DfbVegLVuT4r14eTr6zvA8xSAoBLzx6pvj4l+DZIezuVkG9fY2H7YRQIMZIBwycmzH1/s3F8AapfIPNF3kQk7+kw9PWBy+IZOdg5Ug3mkAATy/t0usovzGeCUWTjCz0B+Sj0ekfdvkZ3abBv+U4GaCtJ1iEm6ANQJ6fEzrG/engcKw/wXQvEKxSEKQxRGKE7Izt+DSiwBJMUSm71rguMYhQKrBygOIRStf4TiFFRBvbRGKiQLWP29yRSHKBTtfdBmHs0BUpgvtgF4yRFR+NUKi0XZcYjCeCG2smkzLAHkbRBmP0/Uk26O5YnUActBp1GsAI+S5nRJJJal5K1aAMrq0d6Tm9uI6zjyf75dAe6tx/SsWeD//o2/Ab6IH3/h25pOAAAAAElFTkSuQmCC)}.php-file-tree LI.pft-command{list-style:none} LI.pft-file{list-style-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAC4SURBVCjPdZFbDsIgEEWnrsMm7oGGfZrohxvU+Iq1TyjU60Bf1pac4Yc5YS4ZAtGWBMk/drQBOVwJlZrWYkLhsB8UV9K0BUrPGy9cWbng2CtEEUmLGppPjRwpbixUKHBiZRS0p+ZGhvs4irNEvWD8heHpbsyDXznPhYFOyTjJc13olIqzZCHBouE0FRMUjA+s1gTjaRgVFpqRwC8mfoXPPEVPS7LbRaJL2y7bOifRCTEli3U7BMWgLzKlW/CuebZPAAAAAElFTkSuQmCC)}</style>

<script>

var MD5=function(r){function n(r,n){return r<<n|r>>>32-n}function t(r,n){var t,o,e,u,f;return e=2147483648&r,u=2147483648&n,t=1073741824&r,o=1073741824&n,f=(1073741823&r)+(1073741823&n),t&o?2147483648^f^e^u:t|o?1073741824&f?3221225472^f^e^u:1073741824^f^e^u:f^e^u}function o(r,n,t){return r&n|~r&t}function e(r,n,t){return r&t|n&~t}function u(r,n,t){return r^n^t}function f(r,n,t){return n^(r|~t)}function i(r,e,u,f,i,a,c){return r=t(r,t(t(o(e,u,f),i),c)),t(n(r,a),e)}function a(r,o,u,f,i,a,c){return r=t(r,t(t(e(o,u,f),i),c)),t(n(r,a),o)}function c(r,o,e,f,i,a,c){return r=t(r,t(t(u(o,e,f),i),c)),t(n(r,a),o)}function C(r,o,e,u,i,a,c){return r=t(r,t(t(f(o,e,u),i),c)),t(n(r,a),o)}function g(r){for(var n,t=r.length,o=t+8,e=(o-o%64)/64,u=16*(e+1),f=Array(u-1),i=0,a=0;t>a;)n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|r.charCodeAt(a)<<i,a++;return n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|128<<i,f[u-2]=t<<3,f[u-1]=t>>>29,f}function h(r){var n,t,o="",e="";for(t=0;3>=t;t++)n=r>>>8*t&255,e="0"+n.toString(16),o+=e.substr(e.length-2,2);return o}function d(r){r=r.replace(/\r\n/g,"\n");for(var n="",t=0;t<r.length;t++){var o=r.charCodeAt(t);128>o?n+=String.fromCharCode(o):o>127&&2048>o?(n+=String.fromCharCode(o>>6|192),n+=String.fromCharCode(63&o|128)):(n+=String.fromCharCode(o>>12|224),n+=String.fromCharCode(o>>6&63|128),n+=String.fromCharCode(63&o|128))}return n}var v,S,m,l,A,s,y,b,p,w=Array(),D=7,L=12,M=17,j=22,k=5,q=9,x=14,z=20,B=4,E=11,F=16,G=23,H=6,I=10,J=15,K=21;for(r=d(r),w=g(r),s=1732584193,y=4023233417,b=2562383102,p=271733878,v=0;v<w.length;v+=16)S=s,m=y,l=b,A=p,s=i(s,y,b,p,w[v+0],D,3614090360),p=i(p,s,y,b,w[v+1],L,3905402710),b=i(b,p,s,y,w[v+2],M,606105819),y=i(y,b,p,s,w[v+3],j,3250441966),s=i(s,y,b,p,w[v+4],D,4118548399),p=i(p,s,y,b,w[v+5],L,1200080426),b=i(b,p,s,y,w[v+6],M,2821735955),y=i(y,b,p,s,w[v+7],j,4249261313),s=i(s,y,b,p,w[v+8],D,1770035416),p=i(p,s,y,b,w[v+9],L,2336552879),b=i(b,p,s,y,w[v+10],M,4294925233),y=i(y,b,p,s,w[v+11],j,2304563134),s=i(s,y,b,p,w[v+12],D,1804603682),p=i(p,s,y,b,w[v+13],L,4254626195),b=i(b,p,s,y,w[v+14],M,2792965006),y=i(y,b,p,s,w[v+15],j,1236535329),s=a(s,y,b,p,w[v+1],k,4129170786),p=a(p,s,y,b,w[v+6],q,3225465664),b=a(b,p,s,y,w[v+11],x,643717713),y=a(y,b,p,s,w[v+0],z,3921069994),s=a(s,y,b,p,w[v+5],k,3593408605),p=a(p,s,y,b,w[v+10],q,38016083),b=a(b,p,s,y,w[v+15],x,3634488961),y=a(y,b,p,s,w[v+4],z,3889429448),s=a(s,y,b,p,w[v+9],k,568446438),p=a(p,s,y,b,w[v+14],q,3275163606),b=a(b,p,s,y,w[v+3],x,4107603335),y=a(y,b,p,s,w[v+8],z,1163531501),s=a(s,y,b,p,w[v+13],k,2850285829),p=a(p,s,y,b,w[v+2],q,4243563512),b=a(b,p,s,y,w[v+7],x,1735328473),y=a(y,b,p,s,w[v+12],z,2368359562),s=c(s,y,b,p,w[v+5],B,4294588738),p=c(p,s,y,b,w[v+8],E,2272392833),b=c(b,p,s,y,w[v+11],F,1839030562),y=c(y,b,p,s,w[v+14],G,4259657740),s=c(s,y,b,p,w[v+1],B,2763975236),p=c(p,s,y,b,w[v+4],E,1272893353),b=c(b,p,s,y,w[v+7],F,4139469664),y=c(y,b,p,s,w[v+10],G,3200236656),s=c(s,y,b,p,w[v+13],B,681279174),p=c(p,s,y,b,w[v+0],E,3936430074),b=c(b,p,s,y,w[v+3],F,3572445317),y=c(y,b,p,s,w[v+6],G,76029189),s=c(s,y,b,p,w[v+9],B,3654602809),p=c(p,s,y,b,w[v+12],E,3873151461),b=c(b,p,s,y,w[v+15],F,530742520),y=c(y,b,p,s,w[v+2],G,3299628645),s=C(s,y,b,p,w[v+0],H,4096336452),p=C(p,s,y,b,w[v+7],I,1126891415),b=C(b,p,s,y,w[v+14],J,2878612391),y=C(y,b,p,s,w[v+5],K,4237533241),s=C(s,y,b,p,w[v+12],H,1700485571),p=C(p,s,y,b,w[v+3],I,2399980690),b=C(b,p,s,y,w[v+10],J,4293915773),y=C(y,b,p,s,w[v+1],K,2240044497),s=C(s,y,b,p,w[v+8],H,1873313359),p=C(p,s,y,b,w[v+15],I,4264355552),b=C(b,p,s,y,w[v+6],J,2734768916),y=C(y,b,p,s,w[v+13],K,1309151649),s=C(s,y,b,p,w[v+4],H,4149444226),p=C(p,s,y,b,w[v+11],I,3174756917),b=C(b,p,s,y,w[v+2],J,718787259),y=C(y,b,p,s,w[v+9],K,3951481745),s=t(s,S),y=t(y,m),b=t(b,l),p=t(p,A);var N=h(s)+h(y)+h(b)+h(p);return N.toLowerCase()};

function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

function newFile(path) {
  location.href = '?newfile=' + path;
}
function newFolder(path) {
  location.href = '?newfolder=' + path;
}
function uploadFile() {
  location.href = '?uploadfile';
}


function initPHPFileTree() {
    if (document.getElementsByTagName) {
        for (var e = document.getElementsByTagName("LI"), n = 0; n < e.length; n++) {
            var t = e[n].className;





            if (t.indexOf("pft-directory") > -1){

              var d = e[n].getElementsByTagName('A')[0].innerHTML;

              if (getCookie(MD5(d))) {
                t += ' open';
              }

                for (var i = e[n].childNodes, l = 0; l < i.length; l++) "A" == i[l].tagName && (i[l].onclick = function() {


var d = this.innerHTML;



                    for (var e = this.nextSibling;;) {

                        
                        if (null == e) return !1;



                        if ("UL" == e.tagName) {
                            var n = "none" == e.style.display;

                              if (n) {document.cookie = MD5(d) + "=" + n;}
                              else {
                                document.cookie = MD5(d) + "=; expires=Thu, 01 Jan 1970 00:00:01 GMT;"
                              }

                            return e.style.display = n ? "block" : "none", this.className = n ? "open" : "closed", !1
                        }
                        e = e.nextSibling
                    }
                    return !1
                }, 
                i[l].className = t.indexOf("open") > -1 ? "open" : "closed"),

                "UL" == i[l].tagName && (i[l].style.display = t.indexOf("open") > -1 ? "block" : "none");
              }
        }
        return !1
    }
}
window.onload = initPHPFileTree;
</script>

<?php

    echo '<button>Delete</button>'."\n".'<button onclick="uploadFile()">Upload file</button>';
    echo '<hr>';
    echo $tree->php_file_tree($_COOKIE['phpshbin_path'], "?edit=[link]");
  }

  $time_end = microtime_float();
  $time = $time_end - $time_start;

  echo '<hr><p>Disk free space: <b>'.human_filesize(disk_free_space('.')).'</b> of '.human_filesize(disk_total_space('.')).'</p>';
  echo '<hr><p>PHPSHBIN <small>version 1.0, elapsed time '.$time.' sec.</small></p></body></html>';
  
}