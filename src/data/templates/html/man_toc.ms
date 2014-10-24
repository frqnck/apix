{{> header }}

<h2>API Name</h2>
	<p><em>{{ config.api_realm }}</em> - <span class="man-ref">{{ config.api_version }}</span></p>

<h2>Overview</h2>
<p>
	This API provides a HTTP based <a href="http://en.wikipedia.org/wiki/REST">RESTful</a> interface for <a href="http://en.wikipedia.org/wiki/CRUD">CRUD</a> operations using the standard <a href="http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Request_methods">HTTP methods</a>. 
</p>
<p>
	Resources are represented by unique URL that on invocation will always return a status code (see <a href="#Response_Codes">#Response Codes</a>) and may also contain a formatted data message (see <a href="#Response_Formats">#Response Formats</a>).
</p>
<h2>API Resources</h2> 
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

<h2 id="Response_Formats">Response Formats</h2>
	<p>
		The responses are available in <b class="uppercase">{{ config.routing.default_format }}</b> by default
		and always include a <tt>Content-Type</tt> header confirming the output format.
	</p>
	{{# hasManyOutputFormats }}
		<h3>Available formats</h3>
		<table border="1" class="formats">
			<colgroup>
				<col width="10%"><col width="60%"><col width="15%"><col width="25%">
			</colgroup>
			<thead valign="bottom">
				<tr>
					<th>Format</th>
					<th>Description</th>
					<th>Extension</th>
					<th>Media Type</th>
				</tr>
			</thead>
			<tbody valign="top">
				{{# getOutputFormats }}<tr>{{{ . }}}</tr>{{/ getOutputFormats }}
			</tbody>
		</table>
		{{ #config.routing.controller_ext }}
			<h3>Filename extension</h3>
			<p>
				The <tt>Content-Type</tt> can be set by appending an extension to
				the resource name such as <pre>GET {{ options.rel_path }}<b>.{{ config.routing.default_format }}</b>/foo</pre>
			</p>
		{{ /config.routing.controller_ext }}

		{{# config.routing.http_accept }}
			<h3>HTTP Accept Headers</h3>
			<p>
				Using one or many <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html">HTTP Accept</a> headers alongside the relevant <a href="http://en.wikipedia.org/wiki/Internet_media_type">Internet media types</a> allow applications to automatically negotiate the most appropriate <tt>Content-Types</tt>.
			</p>
		{{/ config.routing.http_accept }}
	{{/ hasManyOutputFormats }}

<h2>Inputs and data imports</h2>
	<p>This API allow to imports data using either:</p>
	<h3>Path parameters</h3>
	<p>
		Some resources may requires one or many parameters to be passed as path segment, such as:
		<pre>POST /element/<b>:name</b></pre>
	</p>

	<h3>Query parameters</h3>
	<p>
		Some resources parameters may expect one or many parameters to be provided using the request's <a href="http://en.wikipedia.org/wiki/Query_string">query string</a>. These are generally use for filtering purposes. For instance to retrieve fictive <i>some elements</i>:<pre>GET /some_elements<b>?types[]=rock&types[]=jazz&sort=ascending</b></pre>
	</p>
	<h3>Request body</h3>
	<p>
		For more complex data import, some resources accept encoded/formated body content. To submit such request, add the formatted data in the request body and use the relevant <tt>Content-Type</tt> header field:</p>
			<ul>{{# getInputFormats }}<li>{{{ . }}}</li>{{/ getInputFormats }}</ul>
	</p>

<h2 id="Response_Codes">Response Codes</h2>
	<p>
		Wether sucesful or not, a standard <a href="http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Status_codes">HTTP response code</a> will be returned with every single request and convey the general status of the request. For instance, the most common codes are:
		<table>
			<tr><td>200</td><td>Ok</td><td>The request has succeeded.</td></tr>
			<tr><td>201</td><td>Created</td><td>The request has been fulfilled and resulted in a new resource being created.</td></tr>
			<tr><td>202</td><td>Accepted</td><td>The request has been accepted for processing, but the processing has not been completed.</td></tr>
			<tr><td>400</td><td>Bad request</td><td>The request is malformed.</td></tr>
			<tr><td>401</td><td>Unauthorized</td><td>Authentification has failed or missing.</td></tr>
			<tr><td>404</td><td>Not found</td><td>No ressource found at the Request-URI.</td></tr>
		</table>
	<p>
		<b>Errors</b> will contain additional details, such as:
	</p>
<pre>error:
    message: Invalid resource's method (DELETE) specified.
    code: 405 
{{# hasPluginSignature }}signature:
    resource: DELETE /help/:path
    status: 405 Method Not Allowed - failed
{{/ hasPluginSignature }}</pre>

<h2>Datatypes</h2>
<ol>
	<li><b>String</b> generally UTF-8 encoded, or URL encoded for URL/Body.</li>
	<li><b>Boolean</b> <i>True</i> or <i>False</i> value</li>
	<li><b>Integer</b> 32-bit numerical value</li>
	<li><b>Date</b> Date formatted as "yyyy-MM-dd"</li>
	<li><b>Timestamp</b> Date and time formatted as "yyyy-MM-ddTHH:mm:ssZ" (ISO 8601)</li>
	<li><b>Array</b> Array of integers, strings, objects or other types</li>
	<li><b>Object</b> Custom Object described in the Resource's Response</li>
</ol>

{{> footer }}