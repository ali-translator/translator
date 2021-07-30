<?php

namespace ALI\Translator\Source;

interface SourceIdWorkerInterface
{
    /**
     * @param string[] $phrases
     * @return string[]
     */
    public function getOriginalsIds(array $phrases): array;

    /**
     * @param string[] $originalsIds
     * @return string[]
     */
    public function getOriginalsByIds(array $originalsIds): array;
}
