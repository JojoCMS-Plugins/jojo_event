{if $pg_body && (!$pagenum || $pagenum==1) && !$event}{$pg_body}{/if}
{if $event}
  <h3>{$event.title}{if $event.full} <span>##Full!##</span>{elseif $event.cancelled} <span class="important">##Cancelled##</span>{/if}</h3>
    {if $event.event_image}<a href="images/default/events/{$event.event_image}" rel="lightbox" title="full size image"><img src="images/v12000/events/{$event.event_image}" class="float-right" alt="{$event.title}" /></a>{/if}
    <p class="date">
    {$event.fstartdate}{if $event.starttime} ##at## {$event.starttime} {/if}{if $event.fenddate && $event.fenddate != $event.fstartdate} - {$event.fenddate}{/if}
    {if $event.location}<br />{if $event.locationurl}<a style="color:#009fe3;" href="{$event.locationurl}">{$event.location}</a>{else}{$event.location}{/if}
        {if $event.locationmaplink && $event.locationaddress}<br /><a href="{$event.locationmaplink}">{$event.locationaddress}</a>
        {elseif $event.locationmaplink} - <a href="{$event.locationmaplink}">##See map##</a>
        {elseif $event.locationaddress}<br />{$event.locationaddress}{/if}
    {/if}
    {if $event.cost}<br />Cost: {$event.cost}{/if}
    </p>
   {$event.description}
    {if $event.website}<p class="note">For more info see: <a href="{$event.website}">{$event.website|truncate:50}</a></p>{/if}
    {if $event.contactemail}<p class="note">##Contact##: <a href="mailto:{$event.contactemail}?subject={str_replace(' ', '%20', $event.title)}%20{str_replace(' ', '%20', $event.fstartdate)}">{$event.contactemail}</a></p>{/if}
    <div id="event-bottomlinks">
        <p class="links">&lt;&lt; <a href="{if $multilangstring}{$multilangstring}{/if}{if $pg_url}{$pg_url}/{else}{$pageid}/{$pg_title|strtolower}{/if}" title="{$pg_title}">{$pg_title}</a>&nbsp; {if $prevevent}&lt; <a href="{$prevevent.url}" title="Previous">{$prevevent.title}</a>{/if}{if $nextevent} | <a href="{$nextevent.url}" title="Next">{$nextevent.title}</a> &gt;{/if}</p>
    </div>
{elseif $events}
{assign var=prev value=""}
{foreach from=$events item=event}
{if $prev != $event.month}{assign var=prev value=$event.month}
<h2>{$prev}</h2>
{/if}
<div class="event">
  <h3>{$event.title}{if $event.full} <span class="full">##Full!##</span>{elseif $event.cancelled} <span class="important">##Cancelled##</span>{/if}</h3>
    {if $event.event_image}<a href="images/default/events/{$event.event_image}" rel="lightbox" title="full size image"><img src="images/v12000/events/{$event.event_image}" class="float-right" alt="{$event.title}" /></a>{/if}
    <p class="date">
    {$event.fstartdate}{if $event.starttime} at {$event.starttime} {/if}{if $event.fenddate && $event.fenddate != $event.fstartdate} - {$event.fenddate}{/if}{if !$event.full}
    {if $event.location}<br />{$event.location} {if $event.locationurl}<span class="note">(<a href="{$event.locationurl}" title="{$event.location} website" >{str_replace('http://', '', rtrim($event.locationurl, '/'))}</a>)</span>{/if}    {/if}
    {if $event.locationaddress}<br />{$event.locationaddress}{/if}
    {if $event.locationmaplink} <span class="note">- <a href="{$event.locationmaplink}" title="Google map">See Google map</a></span>{/if}
    {if $event.cost}<br />Cost: {$event.cost}{/if}{/if}
    </p>
   {$event.description}
    {if $event.website && !$event.full && !$event.cancelled}<p class="note">##For more info see##: <a href="{$event.website}">{$event.website|truncate:50}</a></p>{/if}
    {if $event.contactemail && !$event.full && !$event.cancelled}<p class="note"><a href="mailto:{$event.contactemail}?subject={str_replace(' ', '%20', $event.title)}%20{str_replace(' ', '%20', $event.fstartdate)}">##Contact us for bookings or more information##</a></p>{/if}
</div>
{/foreach}
{else}
<p>{if $noevent}{$noevent}{else}##There are currently no upcoming events, please check back later.##{/if}</p>
{/if}
