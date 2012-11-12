<dl>
  {{#items}}
    <dt>
      {{#type}}<i>{{type}}</i>{{/type}}
      <code><var>{{name}}</var></code>
      ({{#required}}required{{/required}}{{^required}}optional{{/required}})
    </dt>
    <dd>
      {{#description}}<p>{{description}}</p>{{/description}}
    </dd>
  {{/items}}
  {{^items}}
    <coda>
      <def>@param undefined</def>
    </coda>
  {{/items}}
</dl>