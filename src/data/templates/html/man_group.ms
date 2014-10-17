{{> header }}

    <h1>
    	{{title}}
    		{{^title}}{{#_def}}Group title (class @toc){{/_def}}
    	{{/title}}
    </h1>
    <p>
    	{{description}}
    		{{^description}}{{#_def}}Group description (class description){{/_def}}
    	{{/description}}
    </p>

    {{#methods}}
        <hr>
        {{> help }}
        <hr>
    {{/methods}}

{{> footer }}