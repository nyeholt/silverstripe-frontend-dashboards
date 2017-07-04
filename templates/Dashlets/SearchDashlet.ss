<div class="dashlet-content-anchor search-dashlet-content">
	<% with $SearchForm %>
    <form $FormAttributes >
        $HiddenFields
        <% with $FieldMap %>
        $Terms
        <% end_with %>
        <input name="action_results" value="Go" class="action" id="Form_SearchForm_action_results" type="submit">
    </form>
    <% end_with %>
	<div class="dynamic-search-dashlet-content"></div>
</div>