<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv='content-type' value='text/html;charset=utf8'>
  <title>
    {{^ title }}API manpages for "{{ config.api_realm }}"{{/ title }}
    {{# title  }}{{.}} ({{ config.api_realm }}){{/ title }}
  </title>
<meta content="API manpage for {{ config.api_realm }}/ " name="description">
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
  #man p,#man pre,#man ul,#man ol,#man dl,#man table{margin:0 0 1em 0}
  #man h2 {margin:10px 0 0 0;text-transform:uppercase}
  #man > p,#man > pre,#man > ul,#man > ol,#man > dl, #man > table {margin-left:8ex}

  /*#man samp{float:left;border:1px solid red; display:inline-block;}*/

#man samp span {display:inline-block;} 
#man samp span:first-child::before {content:"?[";color:grey;} 
#man samp span::before {content:"&[";color:grey;} 
#man samp span::after {content:"]";color:grey;} 

  #man h3 {margin:0 0 0 4ex}

  #man dt {margin:0;clear:left}
  #man dt.flush {float:left;padding-right:.2em}
  #man dd {display:table-row}

  #man h1,#man h2,#man h3,#man h4 {clear:left}
  #man pre {margin-bottom:20px}
  #man pre+h2,#man pre+h3 {margin-top:22px}
  #man h2+pre,#man h3+pre {margin-top:5px}
  /*#man h2+pre,h3+pre {padding-left:0}*/

  #man img {display:block;margin:auto}
  #man h1.man-title {display:none}
  #man,#man code,#man pre,#man tt,#man kbd,#man samp,#man h3,#man h4 {
    font-family:monospace;font-size:14px;line-height:1.4;
  }

  #man code, #man pre, #man tt, #man kbd {
    background-color: rgba(0, 0, 0, 0.04);
    border-radius: 3px;
    margin: 0;
    padding: .2em .4em;
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

  #man dl {
    margin-bottom:.5em;
  }
  
  #man pre {
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    display: block;
    font-size: 13px;
    line-height: 1.4;
    padding: 9.5px;
    word-wrap: break-word;
    overflow:hidden;
  }

  #man pre code {font-weight:normal;color:#434241}

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
    #man .bold {font-weight: bold}
    #man .uppercase {text-transform:uppercase}
    #man .center {text-align:center}
    #man .default {color:red;}

/* -- TABLEs -- */
    #man table {
      border-collapse:collapse;
      border-spacing:0px .6ex;
      width:100%;
    }
    #man table th {
      font-weight: bold;
      text-align:left;
    }

    #man table th, #man table td {
        border: 1px solid #ccc;
        padding: 6px 13px;
    }
    #man table tr {
        background-color: #fff;
        border-top: 1px solid #ccc;
        margin:0px 0 20px 0;
    }
    #man table tr:nth-child(2n) {
        background-color: #f8f8f8;
    }

    #man table.formats td a {
      font-weight: bold;
    }


/*
#man td:first-child {
    border-left:1.8ex solid #ddd;
}
*/

/* -- COMPENSATE -- */

#man p+pre {margin-left:5em;margin-top:.5em;}
  /*#man p+pre {margin-top:-1em;}*/
  /*#man p+table {margin-top:-.5em;margin-bottom:.5em;}*/
  /*#man p+pre {margin-top:-1em;}*/

  </style>
</head>
<body>
  <div id='man'>

    <h1 class='man-title'>
      {{title}}{{^title}}<span class="default">#method_title</span>{{/title}}
    </h1>

    <ol class='man-decor man head'>
      <li class='tl'>{{ getCmdSection }}</li>
      <li class='tc'>API Manual</li>
      <li class='tr'>{{ getCmdSection }}</li>
    </ol>