<?php

$module = new \UIOWA\ProjectAggregator\ProjectAggregator();

$module->bulkAggregate($_GET['pid']);