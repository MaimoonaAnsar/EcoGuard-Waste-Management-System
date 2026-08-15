<?php
/* EcoGuard role workflow — informational relationship between system roles. */
$workflowRole = strtolower((string)($_SESSION['role_id'] ?? ''));
?>
<section class="eg-workflow eg-role-workflow" aria-label="EcoGuard waste management workflow">
    <div class="eg-workflow-header">
        <div>
            <span class="eg-eyebrow">ECOGUARD WASTE MANAGEMENT</span>
            <h2>Complaint Coordination</h2>
            <p>Each role handles the part of the waste-management process assigned to it.</p>
        </div>
        <span class="eg-system-status"><span></span> Connected workflow</span>
    </div>
    <div class="eg-flow">
        <div class="eg-role-card"><div class="eg-role-icon">C</div><div class="eg-role-content"><strong>Citizen</strong><small>Report waste issue</small></div></div>
        <div class="eg-flow-arrow"></div>
        <div class="eg-role-card"><div class="eg-role-icon">GN</div><div class="eg-role-content"><strong>GN</strong><small>Local verification</small></div></div>
        <div class="eg-flow-arrow"></div>
        <div class="eg-role-card"><div class="eg-role-icon">LA</div><div class="eg-role-content"><strong>Local Authority</strong><small>Collection &amp; truck schedules</small></div></div>
        <div class="eg-flow-arrow"></div>
        <div class="eg-role-card"><div class="eg-role-icon">DS</div><div class="eg-role-content"><strong>DS</strong><small>Escalation &amp; oversight</small></div></div>
        <div class="eg-flow-arrow"></div>
        <div class="eg-role-card"><div class="eg-role-icon">A</div><div class="eg-role-content"><strong>Administrator</strong><small>System monitoring</small></div></div>
    </div>
</section>
