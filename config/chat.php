<?php

return [
    /** Seconds without heartbeat before an agent is considered offline */
    'agent_presence_ttl' => (int) env('CHAT_AGENT_PRESENCE_TTL', 120),
];
