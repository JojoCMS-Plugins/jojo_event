{if $pg_body && (!$pagenum || $pagenum==1) && !$event}{$pg_body}{/if}
{if $event}
  <h3>{$event.title}</h3>
    {if $event.event_image}<a href="images/default/events/{$event.event_image}" rel="lightbox" title="full size image"><img src="images/v12000/events/{$event.event_image}" class="float-right" alt="{$event.title}" /></a>{/if}
    <p class="date">
    {$event.fstartdate}{if $event.enddate && $event.enddate != $event.startdate} - {$event.fenddate}{/if}
    {if $event.location}<br />{$event.location}{/if}
    </p>
   {$event.description}
    {if $event.url}<p class="note">For more info see: <a href="{$event.url}">{$event.url|truncate:50}</a></p>{/if}
    {if $event.contactemail}<p class="note">Contact: <a href="mailto:{$event.contactemail}">{$event.contactemail}</a></p>{/if}

{elseif $events}
{assign var=prev value=""}
{foreach from=$events item=event}
{if $prev != $event.month}{assign var=prev value=$event.month}
<h2>{$prev}</h2>
{/if}
<div class="event">
  <h3>{$event.title}</h3>
    {if $event.event_image}<a href="images/default/events/{$event.event_image}" rel="lightbox" title="full size image"><img src="images/v12000/events/{$event.event_image}" class="float-right" alt="{$event.title}" /></a>{/if}
    <p class="date">
    {$event.fstartdate}{if $event.enddate && $event.enddate != $event.startdate} - {$event.fenddate}{/if}
    {if $event.location}<br />{$event.location}{/if}
    </p>
   {$event.description}
    {if $event.url}<p class="note">For more info see: <a href="{$event.url}">{$event.url|truncate:50}</a></p>{/if}
    {if $event.contactemail}<p class="note">Contact: <a href="mailto:{$event.contactemail}">{$event.contactemail}</a></p>{/if}
</div>
{/foreach}
{else}
<p>{if $noevent}{$noevent}{else}There are currently no upcoming events, please check back later.{/if}</p>
{/if}
