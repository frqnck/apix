<h2>SYNOPSIS</h2>
    <p>{{ title }}{{^ title }}{{# _def }}The resource title{{/ _def }}{{/ title }}</p>

    <p>
        <b>Usage</b>
        <samp>
            <span class="uppercase">
                {{ method }}{{^ method }}
                {{# _def }}METHOD{{/ _def }}{{/ method }}
            </span>
            {{{ usage }}}
            {{^ usage }}
                {{ path }}{{^ path }}{{# _def }}/default-path{{/ _def }}{{/ path }}
            {{/ usage }}
        </samp>
    </p>

    <dl>
        <dt class="flush">
            <b>Usage</b>
            <span class="uppercase">
                {{ method }}{{^ method }}
                {{# _def }}METHOD{{/ _def }}{{/ method }}
            </span>
        </dt>
        <dd>
            <samp>
                {{{ getFormatedUsage }}}
                {{^ usage }}
                    {{ path }}{{^ path }}{{# _def }}/default-path{{/ _def }}{{/ path }}
                {{/ usage }}
            </samp>
        </dd>
    </dl>

{{# description }}
<h2>Description</h2>
    <p>{{{ description }}}</p>
{{/ description }}

{{# getResourceParams }}
<h2>{{ title }}</h2>
    <p>{{{ txt }}}</p>
    {{> resource_param }}
{{/ getResourceParams }}


{{# getRequestParams }}
<h2>{{ title }}</h2>
    <p>Some <a href="http://en.wikipedia.org/wiki/Query_string">query string</a> parameters are available for filtering purposes. {{{ txt }}}</p>
    {{> resource_param }}
{{/ getRequestParams }}


{{# getResourceGroups }}
    {{# . }}
      {{> resource_group }}
    {{/ . }}
{{/ getResourceGroups }}
