<h2>SYNOPSIS</h2>
    <p>{{title}}{{^title}}{{#_def}}#method_title{{/_def}}{{/title}}</p>
    {{#usage}}<p>{{.}}</p>{{/usage}}
    {{^usage}}
    <p>
        <b>Usage</b> <samp><span class="uppercase">{{method}}{{^method}}{{#_def}}METHOD{{/_def}}{{/method}}</span>
        {{path}}{{^path}}{{#_def}}/default-path{{/_def}}{{/path}}
        </samp>
    </p>
    {{/usage}}

{{#description}}
<h2>Description</h2>
    <p>{{{description}}}</p>
{{/description}}

{{#params}}
<h2>{{title}}</h2>
    <p>{{txt}}</p>
    {{>param_definition}}
{{/params}}

{{#groups}}
    {{#.}}
      {{>group_definition}}
    {{/.}}
{{/groups}}