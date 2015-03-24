			{{# getResourceGroups }}
			    {{# . }}
			      {{> resource_group }}
			    {{/ . }}
			{{/ getResourceGroups }}

			{{# getVersion }}
			<h2>Vesion information</h2>
			    <p>
			       This API manpage describes {{{ getVersion }}}.
			    </p>
			{{/ getVersion }}

			<ol class='man-decor man-foot man foot'>
			  <li class='tl'>{{ config.api_version }}</li>
			  <li class='tc'></li>
			  <li class='tr'>{{ config.api_realm }}</li>
			</ol>
		</div>
	</body>
</html>