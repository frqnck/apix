{{> header }}

<h2>Name</h2>
    <p>{{ title }}{{^ title }}{{# _def }}The resource title{{/ _def }}{{/ title }}</p>

<h2>Synopsis</h2>
    <dl>
        <dt class="flush">
            <span class="uppercase">
                {{ method }}{{^ method }}{{# _def }}METHOD{{/ _def }}{{/ method }}&nbsp;
            </span>
        </dt>
        <dd>
            <samp>
                {{{ getFormatedUsage }}}
            </samp>
        </dd>
    </dl>

{{# description }}
<h2>Description</h2>
    <p>{{{ description }}}</p>
{{/ description }}

{{# getPathParams }}
<h2>Path parameter{{# items.1 }}s{{/ items.1 }}</h2>
    <p>
        {{# items.1 }}
            The following resource path parameters are available:
        {{/ items.1 }}
        {{^ items.1 }}
            The following resource path parameter is available:
        {{/ items.1 }}
    </p>
    {{> resource_param }}
{{/ getPathParams }}

{{# getQueryParams }}
<h2>Query parameter{{# items.1 }}s{{/ items.1 }}</h2>
    <p>
        {{# items.1 }}
            The following <a href="http://en.wikipedia.org/wiki/Query_string">query string</a> parameters are available:
        {{/ items.1 }}
        {{^ items.1 }}
            The following <a href="http://en.wikipedia.org/wiki/Query_string">query string</a> parameter is available:
        {{/ items.1 }}
    </p>
    {{> resource_param }}
{{/ getQueryParams }}

{{# return }}
    <h2>Successful response</h2>
    <p>
        When a request is successful, the following is returned:
    </p>
    {{# . }}
    <table>
      <colgroup>
        <col width="25%"><col width="75%">
      </colgroup>
      <thead valign="bottom">
        <tr>
          <th>Type</th>
          <th>Description</th>
        </tr>
      </thead>
      <tr>
        <td>
           {{# type }}<i>{{ type }}</i>{{/ type }}
        </td>
        <td>
          {{# description }}{{ description }}{{/ description }}
        </td>
     </tr>
    </table>
    <p>
        {{{ extra }}}
    </p>
    {{/ . }}
{{/ return }}

{{# getResourceGroups }}
    {{# . }}
      {{> resource_group }}
    {{/ . }}
{{/ getResourceGroups }}

{{> footer }}