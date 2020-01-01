<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Sorter;

final class BeforeAfterComparison implements SorterInterface
{
    /**
     * @inheritDoc
     */
    public function sort(array $arrayToSort)
    {
        $configArray = $this->prepare($arrayToSort);
        foreach ($configArray as $code => $data) {
            foreach ($data['before'] as $beforeCode) {
                if (!isset($configArray[$beforeCode])) {
                    continue;
                }
                $configArray[$code]['before'] = array_unique(array_merge(
                    $configArray[$code]['before'],
                    $configArray[$beforeCode]['before']
                ));
                $configArray[$beforeCode]['after'] = array_merge(
                    $configArray[$beforeCode]['after'],
                    array($code),
                    $data['after']
                );
                $configArray[$beforeCode]['after'] = array_unique($configArray[$beforeCode]['after']);
            }
            foreach ($data['after'] as $afterCode) {
                if (!isset($configArray[$afterCode])) {
                    continue;
                }
                $configArray[$code]['after'] = array_unique(array_merge(
                    $configArray[$code]['after'],
                    $configArray[$afterCode]['after']
                ));
                $configArray[$afterCode]['before'] = array_merge(
                    $configArray[$afterCode]['before'],
                    array($code),
                    $data['before']
                );
                $configArray[$afterCode]['before'] = array_unique($configArray[$afterCode]['before']);
            }
        }
        uasort($configArray, array($this, 'compare'));
        return $configArray;
    }

    /**
     * prepare array for sorting
     *
     * @param array $data
     * @return mixed
     */
    private function prepare($data)
    {
        foreach ($data as $code => &$item) {
            $item = $this->prepareItem($code, $item);
        }
        return $data;
    }

    /**
     * @param   string $code
     * @param   $item
     * @return  array
     */
    private function prepareItem($code, $item)
    {
        if (isset($item['before'])) {
            $item['before'] = explode(',', $item['before']);
        } else {
            $item['before'] = [];
        }
        if (isset($item['after'])) {
            $item['after'] = explode(',', $item['after']);
        } else {
            $item['after'] = [];
        }
        $item['_code'] = $code;
        return $item;
    }

    /**
     * @param   array $a
     * @param   array $b
     * @return  int
     */
    protected function compare($a, $b)
    {
        $aCode = $a['_code'];
        $bCode = $b['_code'];
        if (in_array($aCode, $b['after']) || in_array($bCode, $a['before'])) {
            $res = -1;
        } elseif (in_array($bCode, $a['after']) || in_array($aCode, $b['before'])) {
            $res = 1;
        } else {
            $res = 0;
        }
        return $res;
    }
}
