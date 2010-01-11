{if $content}{$content}{/if}

{if $events}

{assign var=prev value=""}
<div id="event">
{foreach from=$events item=event}
{if $prev != $event.month}{assign var=prev value=$event.month}
<h2>{$prev}</h2>
{/if}
  <h3>{$event.title}</h3>
    {if $event.event_image}<a href="images/default/events/{$event.event_image}" rel="lightbox" title="full size image"><img src="images/v12000/events/{$event.event_image}" class="right-image" alt="{$event.title}" /></a>{/if}
    <p class="date">
    {$event.startdate|date_format:"%d %b"}{if $event.enddate && $event.enddate != $event.startdate} - {$event.enddate|date_format:"%d %b"}{/if}
    {if $event.location}<br />{$event.location}{/if}
    </p>
  {$event.description}
{if $event.url}<p class="note">For more info see: <a href="{$event.url}">{$event.url|truncate:50}</a></p>{/if}
{if $event.contactemail}<p class="note">Contact: <a href="mailto:{$event.contactemail}">{$event.contactemail}</a></p>{/if}


{/foreach}
{else}
<p>{if $noevent}{$noevent}{else}There are currently no upcoming events, please check back later.{/if}</p>
{/if}
</div>
