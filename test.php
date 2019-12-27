
<?php
stream_set_read_buffer($socket, 0);
stream_set_write_buffer($socket, 0);

set_time_limit(0);
ini_set('auto_detect_line_endings', 1);
ini_set('max_execution_time', '0');

/* start fresh */
ob_end_clean();

/* ultility function for sending SSE messages */
function sse($evtname = 'sse', $data = null, $retry = 1000)
{
    if (!is_null($data)) {
        echo "event:" . $evtname . "\r\n";
        echo "retry:" . $retry . "\r\n";
        echo "data:" . json_encode($data, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        echo "\r\n\r\n";
    }
}

$id = 0;
$event = 'event1';
$oldValue = null;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Transfer-encoding: chunked');

while (true) {
    ob_start();

    try {
        $data = date('r');
    } catch (Exception $e) {
        $data = $e->getMessage();
    }

    if ($oldValue !== $data) {

        /* data has changed or first iteration */
        $oldValue = $data;

        /* send the sse message */
        sse($event, $data);

        /* make sure all buffers are cleansed */
        if (@ob_get_level() > 0) {
            for ($i = 0; $i < @ob_get_level(); $i++) {
                @ob_flush();
            }
        }

        @flush();
        ob_end_flush();

    }

    /*
    sleep each iteration regardless of whether the data has changed or not....
     */
    sleep(10);
}
