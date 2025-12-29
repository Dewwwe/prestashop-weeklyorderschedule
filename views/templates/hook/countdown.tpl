<!--
 * Countdown Timer Template - Weekly Order Schedule Module
 *
 * Displays a dynamic countdown that adapts to the weekly order schedule:
 * - STATE 'open' or 'reopening': Shows live countdown timer (days:hours:minutes)
 * - STATE 'always_open': Shows "orders open" message without countdown
 * - STATE 'closed': Shows "closed" message without countdown
 *
 * Available Smarty variables:
 * - $countdown_state: 'open', 'reopening', 'closed', or 'always_open'
 * - $countdown_deadline: Unix timestamp for the countdown target
 * - $countdown_subtitle: Message displayed below countdown/instead of countdown
 * - $countdown_button_text: Button label (null if no button)
 * - $countdown_button_url: Button destination URL
 *
 * JavaScript countdown updates every 60 seconds (1 minute)
 * Deadline is at 23:59 of the day before the OFF/ON transition
-->

<!-- DEBUG: State={$countdown_state|default:'NOT_SET'} Deadline={$countdown_deadline|default:'NOT_SET'} -->
<div class="cms-card flex-column countdown-card">
    {if $countdown_state == 'closed'}
    {* STATE 3: All days closed - No countdown, just message *}
    <h3>{$countdown_title}</h3>
    <p class="countdown-closed-message">{$countdown_subtitle}</p>
    {elseif $countdown_state == 'always_open'}
    {* All days open - No countdown needed *}
    <h3>{$countdown_title}</h3>
    <p class="countdown-open-message">{$countdown_subtitle}</p>
    {if $countdown_button_text}
    <a href="{$countdown_button_url}" class="btn btn-primary">{$countdown_button_text}</a>
    {/if}
    {else}
    {* STATE 1 (open) or STATE 2 (reopening): Show countdown *}
    <h3>{$countdown_title}</h3>
    <div class="countdown-timer" data-deadline="{$countdown_deadline}">
        <div class="countdown-box">
            <span class="countdown-number" id="days">00</span>
            <span class="countdown-label">jours</span>
        </div>
        <span class="countdown-separator">:</span>
        <div class="countdown-box">
            <span class="countdown-number" id="hours">00</span>
            <span class="countdown-label">heures</span>
        </div>
        <span class="countdown-separator">:</span>
        <div class="countdown-box">
            <span class="countdown-number" id="minutes">00</span>
            <span class="countdown-label">mins</span>
        </div>
    </div>
    <p class="countdown-subtitle">{$countdown_subtitle}</p>
    {if $countdown_button_text}
    <a href="{$countdown_button_url}" class="btn btn-primary">{$countdown_button_text}</a>
    {/if}

    <script>
        (function () {
            const deadline = {$countdown_deadline} * 1000;

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    document.getElementById('days').textContent = '00';
                    document.getElementById('hours').textContent = '00';
                    document.getElementById('minutes').textContent = '00';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

                document.getElementById('days').textContent = String(days).padStart(2, '0');
                document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 60000);
        })();
    </script>
    {/if}
</div>