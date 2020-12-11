<?php
ini_set('memory_limit','-1');
// make sure browsers see this page as utf-8 encoded HTML
include 'SpellCorrector.php';
include 'simple_html_dom.php';
header('Content-Type: text/html; charset=utf-8');
$currentdiv=false;
$correct = "";
$actualcorrect="";
$output = "";
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
if ($query)
{
  $choice = isset($_REQUEST['sort'])? $_REQUEST['sort'] : "default";
  
  require_once('Apache/Solr/Service.php');
  
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
 
  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  try
  {
    if($choice != "default")
 $additionalParameters=array('sort' => 'external_pageRankFile.txt desc');
  
    else{
    $additionalParameters=array('sort' => '');
     
    }
    $curword1 = explode(" ",$query);
    $spell = $curword1[sizeof($curword1)-1];
    for($i=0;$i<sizeOf($curword1);$i++){
     
      $che = SpellCorrector::correct($curword1[$i]);
      if($correct!="")
        $correct = $correct."+".trim($che);
      else{
        $correct = trim($che);
      }
        $actualcorrect = $actualcorrect." ".trim($che);
    }
    $actualcorrect = str_replace("+"," ",$correct);
    $currentdiv=false;
    if(strtolower($query)==strtolower($actualcorrect)){
      $results = $solr->search($query, 0, $limit, $additionalParameters);
    }
    else {
      $currentdiv =true;
      $results = $solr->search($query, 0, $limit, $additionalParameters);
      $link = "http://localhost/solr-php-client/myscript2.php?q=$correct&sort=$choice";
      $output = "Did you mean: <a href='$link'>$actualcorrect</a>";
    }
   
  }
  catch (Exception $e)
  {
   
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}
?>
<html>
  <head>
    <title>Sajal Kumar Goel Assignment 5</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

<style>
body{
background-color:lightblue;
}
table{
border: 2px solid pink; width=500px;
}

#dec{
text-decoration:none;

}

#fonttype{
font-weight: bold;
font-size: 6px;
}
</style>
  </head>
  <body>
     <h3> Sajal Kumar Goel - CSCI 572 - Enhanced Search Engine with Auto complete and autosuggest</h3><br/><br>
    <form accept-charset="utf-8" method="get" id="searchform" >
      Search: <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" list="myresults" autocomplete="off"/>
      <datalist id="myresults"></datalist>
      <input type="hidden" name="spellcheck" id="spellcheck" value="false"> <br><br>
        <input type="radio" name="sort1" <?php if (isset($_GET['sort1']) && $_GET['sort1']=="lucene") echo 'checked="checked"';?>  value="lucene" /> Lucene(Default)
	<input type="radio" name="sort1" <?php if (isset($_GET['sort1']) && $_GET['sort1']=="pagerank") echo 'checked="checked"';?> value="pagerank" /> PageRank <br><br>
      <input type="submit" value="Submit"/>
      
    </form>
    <script>
   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;
     var tags = [];
     $("#q").autocomplete({
       source : function(request, response) {
         var correct="",before="";
         var query = $("#q").val().toLowerCase();
         var character_count = query.length - (query.match(/ /g) || []).length;
         var space =  query.lastIndexOf(' ');
         if(query.length-1>space && space!=-1){
          correct=query.substr(space+1);
          before = query.substr(0,space);
        }
        else{
          correct=query.substr(0); 
        }
        var URL = URL_PREFIX + correct+ URL_SUFFIX;
        $.ajax({
         url : URL,
         success : function(data) {
          var js =data.suggest.suggest;
          var docs = JSON.stringify(js);
          var jsonData = JSON.parse(docs);
          var result =jsonData[correct].suggestions;
          var j=0;
          var stem =[];
          for(var i=0;i<5 && j<result.length;i++,j++){
            if(result[j].term==correct)
            {
              i--;
              continue;
            }
            for(var k=0;k<i && i>0;k++){
              if(tags[k].indexOf(result[j].term) >=0){
                i--;
                continue;
              }
            }
            if(result[j].term.indexOf('.')>=0 || result[j].term.indexOf('_')>=0)
            {
              i--;
              continue;
            }
            var s =(result[j].term);
            if(stem.length == 5)
              break;
            if(stem.indexOf(s) == -1)
            {
              stem.push(s);
              if(before==""){
                tags[i]=s;
              }
              else
              {
                tags[i] = before+" ";
                tags[i]+=s;
              }
            }
          }
          console.log(tags);
          response(tags);
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
 </script>
<?php
if ($currentdiv){
  echo $output;
}
$csvArray =  array_map('str_getcsv', file('URLtoHTML_nytimes_news.csv'));
$count =0;
$pre="";
// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
    $searchterm = $_GET["q"];
    $try = explode(" ", $searchterm);
    $id = $doc->id;
 
    $id = str_replace("/home/sajal/Downloads/NYTIMES/nytimes/nytimes/","",$id);
    $descp = $doc->og_description;
  $title = $doc->title;

  if($title=="") 
    
       $title="N/A";

if($descp==null)
    $descp="N/A";
	if($descp=="")
$descp="N/A";

 if($descp==null)

       $descp="N/A";


    foreach ($csvArray as $key ) {
      
        $link = $key[1];
     
    }
   
  
    $html_to_text_files_dir = "/home/sajal/Downloads/NYTIMES/nytimes/nytimes/";
    $filename = $html_to_text_files_dir . $id;
    $html = file_get_contents($filename);
    $mysentence = explode(".", $html);
    $myword = explode(" ", $query);
    $snippet = "";
    $text = "/";
    $mystartdim="(?=.*?\b";
   $myenddim="\b)";
    foreach($myword as $item){
      $text=$text.$mystartdim.$item.$end_delim;
    }
    $text=$text."^.*$/i";
    foreach($mysentence as $sentence){
      $sentence=strip_tags($sentence);
      if (preg_match($text, $sentence)>0){
        if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
          continue;
        }
        else{
          $snippet = $snippet.$sentence;
          if(strlen($snippet)>160) 
            break;
        }
      }
    }
    $myword = preg_split('/\s+/', $query);
  foreach($myword as $item)
	$snippet = str_ireplace($item, "<b>".$item."</b>",$snippet);
    if($snippet == ""){
      $snippet = "N/A";
    }
  
?>
      <li>
        <table>
          <tr>
           <?php echo "<a href = '{$link}'id='dec' class='fonttype'>".$title."</a>" ?>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("Link", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo "<a href = '{$link}' id='dec'><st>".$link."</st></a>" ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("ID", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("Description", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($descp, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars("Snippet :", ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
            if($snippet == "N/A"){
              echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8');
            }else{
              echo "...".$snippet."...";
            }
            ?></td>
          </tr>
          <tr>
            <th><br></th>
            <td><br></td>
          </tr>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
