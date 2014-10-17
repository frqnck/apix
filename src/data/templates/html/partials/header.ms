<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv='content-type' value='text/html;charset=utf8'>
  <title>
    {{ title }}{{^ title }}#method_title{{/ title }} -- {{ id }}{{^ id }}@id{{/ id }}
  </title>

  <style type='text/css' media='all'>
  /*
    <em>  Renders as emphasized text
    <strong>  Defines important text
    <dfn>   Defines a definition term
    <code>  Defines a piece of computer code
    <samp>  Defines sample output from a computer program
    <kbd>   Defines keyboard input
    <var>   Defines a variable
  */
  body {margin:0}
  #man {max-width:100ex;padding:0 9ex 1ex 4ex}
  #man p,#man pre,#man ul,#man ol,#man dl {margin:0 0 20px 0}
  #man h2 {margin:10px 0 0 0;text-transform:uppercase}
  #man > p,#man > pre,#man > ul,#man > ol,#man > dl {margin-left:8ex}

  /*#man samp{float:left;border:1px solid red; display:inline-block;}*/

#man samp span {display:inline-block} 
#man samp span:first-child::before {content:"?[";} 
#man samp span::before {content:"&[";} 
#man samp span::after {content:"]";} 

  #man h3 {margin:0 0 0 4ex}

  #man dt {margin:0;clear:left}
  #man dt.flush {float:left;padding-right:.6em}
  #man dd {display:table-row}
  #man h1,#man h2,#man h3,#man h4 {clear:left}
  #man pre {margin-bottom:20px}
  #man pre+h2,#man pre+h3 {margin-top:22px}
  #man h2+pre,#man h3+pre {margin-top:5px}
  #man img {display:block;margin:auto}
  #man h1.man-title {display:none}
  #man,#man code,#man pre,#man tt,#man kbd,#man samp,#man h3,#man h4 {
    font-family:monospace;font-size:14px;line-height:1.4
  }
  #man h2 {font-size:16px;line-height:1.25}
  #man h1 {font-size:20px;line-height:2}
  /*#man {text-align:justify}*/
  #man,#man code,#man pre,#man pre code,#man tt,#man kbd,#man samp {color:#131211}
  #man h1,#man h2,#man h3,#man h4 {color:#030201}
  #man u {text-decoration:underline}
  #man code,#man strong,#man b {font-weight:bold;color:#131211}
  #man em,#man var {font-style:italic;color:#232221;text-decoration:none}
  #man a,#man a:link,#man a:hover,#man a code,#man a pre,#man a tt,#man a kbd,#man a samp {color:#0000ff}
  #man b.man-ref {font-weight:normal;color:#434241}
  #man pre {padding:0 4ex}
  #man pre code {font-weight:normal;color:#434241}
  #man h2+pre,h3+pre {padding-left:0}
  
  ol.man-decor,ol.man-decor li {
    margin:3px 0 10px 0;padding:0;float:left;width:33%;list-style-type:none;
    text-transform:uppercase;color:#999;letter-spacing:1px
  }
  ol.man-decor {width:100%}
  ol.man-decor li.tl {text-align:left}
  ol.man-decor li.tc {text-align:center;letter-spacing:4px}
  ol.man-decor li.tr {text-align:right;float:right}

/*    #man ol.man a { color:#999 }
    #man ol.man a:hover { color:#333231 }
*/
/* TABLE */
    #man table {
      margin-left:7ex;
      border-collapse:separate;
      border-spacing:0px .6ex;
    }
    #man tr {
      background:#edeceb;
      margin:0px 0 20px 0;
    }
    #man td:first-child {
      border-left:1.8ex solid #ddd;
    }
    #man td {
      vertical-align:top;
      min-width:3.5em;
      padding:5px 7px;
    }
    #man td i {font-family:courrier}

    #man .uppercase {text-transform:uppercase}
    #man .center {text-align:center}
    #man .default {color:red;}

    code.grey {
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: #333;
        display: block;
        font-size: 13px;
        line-height: 1.4;
        padding: 9.5px;
        word-break: break-all;
        word-wrap: break-word;
    }

  </style>
</head>
<body>
  <div id='man'>

    <h1 class='man-title'>
      {{title}}{{^title}}<span class="default">#method_title</span>{{/title}}
    </h1>

    <ol class='man-decor man head'>
      <li class='tl'>{{ config.api_realm }}</li>
      <li class='tc'>Manual</li>
      <li class='tr'>{{ config.api_version }}</li>
    </ol>