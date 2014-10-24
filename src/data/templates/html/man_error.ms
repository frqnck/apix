{{> header}}

<h1>This is an error page!</h1>

<h2>Name</h2>
    <p>{{ title }}{{^ title }}{{# _def }}The resource title{{/ _def }}{{/ title }}</p>

{{# description }}
<h2>Description</h2>
    <p>{{{ description }}}</p>
{{/ description }}

<h2>API resources</h2> 
<p>The API is accesible at <tt>{{ options.url_api }}</tt> and expose the following resources:</p>
<table border="1">
	<thead valign="bottom">
		<tr>
			<th>Location: <tt>{{ options.url_api }}</tt></th>
		</tr>
	</thead>
	<tbody valign="top">
		{{# getResources }}
			<tr>
				<td>
					<b><a href="{{ options.url_help }}{{ resource }}{{ querystr }}"
						title="{{ method }} {{ options.url_help }}{{ resource }}">
						{{ method }}{{^ method }}{{# _def }}METHOD{{/ _def }}{{/ method }}
					</a></b>
					<a href="{{ options.url_help }}{{ resource }}{{ querystr }}"
						title="{{ method }} {{ options.url_help }}{{ resource }}">
						{{ resource }}{{^ resource }}{{# _def }}/resource{{/ _def }}{{/ resource }}
					</a>
				</td>
			</tr>
		{{/ getResources }}
	</tbody>
</table>

{{> footer}}