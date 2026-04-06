<?php

namespace App\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Support\Facades\Log;

trait LogsTable
{
  /**
   * @param array $headers Table headers
   * @param array $rows Table data
   * @param string $title Header title for the log
   * @param string|null $channel Optional custom log channel
   */
  public function logAsTable(array $headers, array $rows, string $title = 'Task Report', string $channel = null)
  {
    if (empty($rows)) {
      $this->getWriter($channel)->info("$title: No data to display.");
      return;
    }

    $buffer = new BufferedOutput();
    $table = new Table($buffer);

    $table->setHeaders($headers)->setRows($rows);
    $table->render();

    $tableString = $buffer->fetch();
    $output = "\n--- $title ---\n" . $tableString;

    // Log to specific channel or default
    $this->getWriter($channel)->info($output);

    // Still show in console if applicable
    if (app()->runningInConsole()) {
      $this->line($tableString);
    }
  }

  /**
   * Helper to determine which log writer to use
   */
  protected function getWriter(?string $channel)
  {
    return Log::channel("shift");
  }
}
