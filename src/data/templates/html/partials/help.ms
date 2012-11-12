<h2>Name</h2>
  <p>
    {{title}}{{^title}}<def>#method_title</def>{{/title}}
    {{#id}}-- {{id}}{{/id}}{{^id}}-- <def>@id</def>{{/id}}
  </p>

<h2>Description</h2>
    <p>{{{description}}}{{^description}}<def>#method_description</def>{{/description}}</p>

{{#params}}
    <h2>{{title}}</h2>
    <p>{{txt}}</p>
    {{> param_definition}}
{{/params}}

<h2>Usage</h2>
    <p>This resource is accessible over HTTP using:</p>
    {{#usage}}
        <ol>
            {{#items}}
            <li><coda>
                    <b class="uppercase">{{method}}{{^method}}<def>XXX</def>{{/method}}</b>
                    {{path}}{{^path}}<def>/default-path</def>{{/path}}
                </coda>
                {{txt}}
            </li>
            {{/items}}
        </ol>
    {{/usage}}
    {{^usage}}
        <ul>
            <li><coda>
                    <b class="uppercase">{{method}}{{^method}}<def>XXX</def>{{/method}}</b>
                    {{path}}{{^path}}<def>/default-path</def>{{/path}}
                </coda>
                e.g. <i>http://www.ouarz.net/api/v2{{path}}{{^path}}<def>/default-path</def>{{/path}}</i>
            </li>
        </ul>
    {{/usage}}

{{#groups}}
    {{#.}}
      {{> group_definition}}
    {{/.}}
{{/groups}}