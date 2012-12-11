{{> header }}

    <h1>{{title}}{{^title}}<span class="default">Group title (class @toc)</span>{{/title}}</h1>
    <p>{{description}}{{^description}}<span class="default">Group description (class description)</span>{{/description}}</p>

    {{#methods}}
        <hr>
        {{> help }}
    {{/methods}}

{{> footer }}