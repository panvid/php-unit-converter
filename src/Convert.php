<?php

declare(strict_types=1);

namespace Panvid\UnitConverter;

class Convert
{
	private float $number;

    /** @var float[]|int[] */
	private array $steps;

    /** @var float|int|null */
	private $min;

    /** @var float|int|null */
	private ?int $max;

	private ?string $prevDigit = null;
	private ?string $pastDigit = null;
	private array $result = [];
	private array $rank = [];
	private array $factor = [];
	private array $base = [];

	/**
	 * @param float[]|int[] $steps
     * @param null|float|int $min
     * @param null|float|int $max
	 */
	public function __construct(
		int $number,
		int $base,
		array $steps,
		string $digit = null,
		$min = null,
		$max = null
	) {
		$this->number = $number * (10 ** $base);
		$this->steps = $steps;
		$this->min = $min;
		$this->max = $max;

		if ($digit !== null) {
			[$this->prevDigit, $this->pastDigit] = explode(".", $digit, 2);
		}

		$this->calculate();
		$this->rank();
		$this->prepare();
	}

	private function calculate(): void
	{
		// calculate all possibilities and rank them
		foreach ($this->steps as $base) {
			// delete value if not in range $min and $max
			if ($this->min !== null && ($this->number / $base) < $this->min) {
				continue;
			}

			if ($this->max !== null && ($this->number / $base) > $this->max) {
				continue;
			}

			// filter values bigger than 10^14 or 10^-13), PHP only save 14 digits without rounding (64Bit IEEE)
			if ($this->number/$base >= (10 ** 14) || $this->number/$base <= (10 ** -15)) {
				continue;
			}

			$this->result[(string) $base] = (float) $this->number / $base;
		}
	}

	private function rank(): void
	{
		foreach ($this->result as $unity => $number) {
			// initialize rank
			$rank = 0;

			// get array before and after dot
			$explode = explode(".", number_format($number,14), 2);
			$prevNumber = $explode[0];
			$pastNumber	= $this->deleteZeros($explode[1], "last");

			// get length and significant numbers of before and after dot
			$prevCount = strlen($prevNumber);
			$pastCount = strlen($pastNumber);
			$prevSigCount = strlen($this->deleteZeros($prevNumber, "last"));
			$pastSigCount = strlen($this->deleteZeros($pastNumber, "first"));

			// calculation
			$rank -= abs($pastCount - $prevCount);
			if ($pastCount > $prevCount) {
				$rank++;
			}

			if ($prevSigCount === 1) {
				$rank += 4;
			}
			if ($prevSigCount === 2) {
				$rank += 2;
			}
			if ($prevSigCount === 3) {
				$rank++;
			}

			if ($prevNumber !== 0) {
				$rank += 2;
			}

			if ($pastSigCount === 0) {
				$rank += 2;
			}
			if ($pastSigCount === 1) {
				$rank += 2;
			}
			if ($pastSigCount === 2) {
				$rank += 3;
			}
			if ($pastSigCount === 3) {
				$rank += 3;
			}
			if ($pastSigCount === 4) {
				$rank++;
			}

            $base = $this->getBase($number);

			if ($base === 0) {
				$rank += 3;
			}
			if ($base % 3 === 0) {
				$rank += 2;
			}
			if ($this->prevDigit !== null && $this->pastDigit !== null) {
				$rank = $rank - abs($pastSigCount - $this->pastDigit) - abs($prevSigCount - $this->prevDigit);
			}

			$this->rank[(string) $unity] = $rank;
		}

		arsort($this->rank, SORT_NUMERIC);
	}

	private function prepare(): void
	{
		foreach ($this->rank as $base => $rank)
		{
			$this->factor[] = $this->result[$base];
			$this->base[] = $base;
		}
	}

	private function getBase($number): int
	{
		$base = (string) ($this->number / $number);
		$explode = explode(".", $base, 2);

		return !isset($explode[1]) ? strlen($base) - 1 : 0 - strlen($explode[1]);
	}

	private function deleteZeros($number, $where): string
	{
		$return = '';
		$array = str_split($number);

		if ($where === "first") {
			$array = array_reverse($array);
		}

		foreach ($array as $value) {
			$return .= $value;
			if ($value !== 0) {
				break;
			}
		}

		if ($where === "first") {
			$return = implode('', array_reverse(str_split($return)));
		}

		return $return;
	}

	public function debug(): void
	{
		echo "<h3>Value:</h3><pre>";
		print_r($this->result);
		echo "</pre><br/><h3>Ranking:</h3><pre>";
		print_r($this->rank);
		echo "</pre><hr/>";
	}

	public function getResult(int $id = 0): ?array
	{
		return !isset($this->factor[$id]) ? null : [$this->base[$id] => $this->factor[$id]];
	}
}
