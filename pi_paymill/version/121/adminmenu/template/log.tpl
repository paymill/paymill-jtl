<table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td>
            <form method="POST" style="width: auto; display: inline;">
                <input name="search_key"/><input type="submit" value="Search" name="submit" style="margin-left: 10px; margin-right: 10px;"/>
                <input type="checkbox" name="connected" value="true" style="margin-right: 5px;">Connected Search
                <input name="kPlugin" type="hidden" value="{$oPlugin->kPlugin}" />
                <input type="hidden" name="cPluginTab" value="Log" />
            </form>
            <form method="POST" style="width: auto; display: inline;">
                <input type="submit" value="Reset Filter" name="reset_filter" style="margin-left: 10px; margin-right: 10px;"/>
                <input name="kPlugin" type="hidden" value="{$oPlugin->kPlugin}" />
                <input type="hidden" name="cPluginTab" value="Log" />
            </form>
            <b>Page: </b>
            {assign var=b value="1"}
            {section name=foo start=$a loop=$pageCount step=1}
                {if $page == $b}
                    <span style="font-weight: bold;">{$b}</span>
                {else}
                    <span style="font-weight: bold;"><a href="{$pageUrl}?kPlugin={$oPlugin->kPlugin}&seite={$b}&cPluginTab=Log">{$b}</a></span>
                {/if}
                {assign var=b value=$b+1}
            {/section}
            <table width="100%" style="margin-top: 10px; margin-bottom: 10px;">
                <tr>
                    <th>Connector ID</th>
                    <th>ID</th>
                    <th>Message</th>
                    <th>Debug</th>
                    <th>Date</th>
                </tr>
                {foreach from=$data item=log}
                    <tr>
                        <td><center>{$log->identifier}</center></td>
                        <td><center>{$log->id}</center></td>
                        <td>{$log->message}</td>
                        <td>
                        {if $log->debug|count_characters < 300}
                            <pre>{$log->debug}</pre>
                        {else}
                            <center>
                                <form method="POST" style="float: right;" action="{$pageUrl}?id={$log->id}&kPlugin={$oPlugin->kPlugin}&cPluginTab=Log">
                                    <input type="submit" value="See more" name="send"/>
                                    <input type="hidden" name="cPluginTab" value="Log" />
                                    <input name="kPlugin" type="hidden" value="{$oPlugin->kPlugin}" />
                                </form>
                            </center>
                        {/if}
                        </td>
                        <td>
                            <center>{$log->date}</center>
                        </td>
                    </tr>
                {/foreach}
            </table>
            <form method="POST" style="width: auto; display: inline;">
                <input name="search_key"/><input type="submit" value="Search" name="submit" style="margin-left: 10px; margin-right: 10px;"/>
                <input type="checkbox" name="connected" value="true" style="margin-right: 10px;">Connected Search
                <input name="kPlugin" type="hidden" value="{$oPlugin->kPlugin}" />
                <input type="hidden" name="cPluginTab" value="Log" />
            </form>
            <form method="POST" style="width: auto; display: inline;">
                <input type="submit" value="Reset Filter" name="reset_filter" style="margin-left: 10px; margin-right: 10px;"/>
                <input name="kPlugin" type="hidden" value="{$oPlugin->kPlugin}" />
                <input type="hidden" name="cPluginTab" value="Log" />
            </form>
            <b>Page: </b>
            {assign var=b value="1"}
            {section name=foo start=$a loop=$pageCount step=1}
                {if $page == $b}
                    <span style="font-weight: bold;">{$b}</span>
                {else}
                    <span style="font-weight: bold;"><a href="{$pageUrl}?kPlugin={$oPlugin->kPlugin}&seite={$b}&cPluginTab=Log">{$b}</a></span>
                {/if}
                {assign var=b value=$b+1}
            {/section}
        </td>
    </tr>
</table>