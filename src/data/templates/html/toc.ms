{{> header}}

    <h1>TOC page</h1>

<h2>Introduction</h2>
<p>This technical documentation describes how to use v1 of the API.</p>
<h3>tt</h3>
<p>das</p>
<h2>API resources</h2>
<ul>
{{#items}}
{{#methods}}
{{#toc}}

    <li>
    	<a href='{{help_path}}{{path}}?method={{method}}' title="{{method}} {{path}}">
			{{toc}}
		</a>
    </li>
{{/toc}}
{{/methods}}
{{/items}}
</ul>

<h2>API structure</h2>
<p></p>
<h3>Parameters</h3>
<p>Blahhh</p>
<h3>Variable types</h3>
<p>
boolean    true/false value
integer 32-bit integer
double  decimal number
string  UTF-8 string in JSON/XML or URL encoded string in URL query
date        date formatted as "yyyy-MM-dd", e.g. "2012-01-05" for 5th January 2012
timestamp   date and time formatted as "yyyy-MM-ddTHH:mm:ssZ" (ISO 8601)
array   array of int, string, object or other types
object  custom type, additionally described in the documentation
</p>
<h3>Blahhhhhh</h3>
<p>Blahhh</p>

{{> footer }}