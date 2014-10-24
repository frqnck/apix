{{# items.0 }}
<table>
  <colgroup>
    <col width="25%"><col width="15%"><col width="60%">
  </colgroup>
  <thead valign="bottom">
    <tr>
      <th>Field</th>
      <th>Type</th>
      <th>Description</th>
    </tr>
  </thead>
  {{# items }}
  <tr>
    <td>
      <var>{{ prefix }}<b>{{ name }}</b></var>
      <sup>{{# required }}*{{/ required }}</sup>
    </td>
    <td>
       {{# type }}<i>{{ type }}</i>{{/ type }}
    </td>
    <td>
      {{# description }}{{ description }}{{/ description }}
    </td>
    </tr>
  {{/ items }}
</table>
{{# items.0.required }}<p><sup><b>*</b></sup> indicates a required parameter.</p>{{/ items.0.required }}
{{/ items.0 }}