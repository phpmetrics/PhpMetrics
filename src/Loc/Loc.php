<?php
namespace Loc;

class Loc {

    public function calculate($file) {

        $files = array($file);
        $analyser = new \SebastianBergmann\PHPLOC\Analyser();
        $data = $analyser->countFiles($files, false);

        $info = new Result;
        $info
            ->setLoc($data['loc'])
            ->setLogicalLoc($data['ncloc'])
            ->setComplexityCyclomatic($data['ccn']);

        return $info;
    }

}