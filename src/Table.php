<?php

/*
 * This file is part of the Miloske85\php-cli-table project
 *
 * Copyright 2016 Milos Milutinovic <milos.milutinovic@live.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Miloske85\php_cli_table;

/**
 * Genarates formatted tables for CLI output
 */
class Table {

    /**
     * Table header, array of names
     * @var array
     */
    private $header = array();

    /**
     * Data for the table, array of arrays (rows)
     * @var array of arrays
     */
    private $data = array();

    /**
     * Maximum length of each column
     * @var array if ints
     */
    private $maxLength = array();

    /**
     * Number of columns
     * @var int
     */
    private $columns;

    /**
     * Line at the start and the end of the table
     * @var string
     */
    private $line;

    /**
     * The final table
     * @var string
     */
    private $table;

    /**
     *
     * @param array $data Array of arrays or objects
     * @param array $header Table header [optional]
     * 
     * Table expects $data to be either array of objects or array of arrays. In 
     * case array of numerically indexed arrays is used, header array must be
     * passed, because the script has no way to generate table header. $header
     * can also be used for passing custom table headers.
     */
    public function __construct($data, $header = null){
        
        $this->data = $data;
        
        $header ? $this->header = $header : $this->header = null;

        $header? $this->columns = count($header) : $this->columns = null;
        
        $this->prepareData();
        
        $this->verifyHeader();
      
        $this->verifyData();

        $this->getLengths();

        $this->generateHeader();
        $this->generateBody();
    }

    /**
     * Get the generated table
     *
     * @return string
     */
    public function getTable(){
        return $this->table;
    }
    
    //==========================================================================
    // Private methods
    //==========================================================================

    private function prepareData(){
        if(!is_array($this->data)){
            throw new \Exception('Data passed must be an array');
        }
        
        if(is_object($this->data[0])){
            $this->generateHeaderFromData();
            $this->convertObjectToArray();
        }
        elseif(is_array($this->data[0])){
            $this->generateHeaderFromData();
        }
        else{
            throw new \Exception('Passed data must be array of objects or arrays');
        }
    }
    
    private function convertObjectToArray(){
        $temp = array();
        
        foreach($this->data as $obj){
            $arr = array();
            
            foreach($obj as $item){
                $arr[] = $item;
            }
            
            $temp[] = $arr;
        }
        
        $this->data = $temp;
    }
    
    private function generateHeaderFromData(){
        //do not overwrite
        if(!$this->header){
            $temp = array();

            foreach($this->data[0] as $key => $item){
                $temp[] = $key;
            }

            $this->header = $temp;

            $this->columns = count($temp);
        }
    }
    
    private function generateHeader(){

        $table = '';

        //starting line
        for($i=0; $i<$this->columns; $i++){
            $table .= '+';
            $len = $this->maxLength[$i] + 2; //ensures that the longest string has a space after it
            $table .= sprintf("%'-{$len}s",'');
        }
        $table .= '+'.PHP_EOL;

        $this->line = $table; //the first and the last line of the header

        //column names
        for($i=0; $i<$this->columns; $i++){
            $len = $this->maxLength[$i] + 1; //ensures that the longest string has a space after it
            $table .= '| ';
            $table .= sprintf("%' -{$len}s",$this->header[$i]);
        }

        $table .= '|'.PHP_EOL;

        //add the ending line
        $table .= $this->line;

        $this->table = $table;
    }

    private function generateBody(){
        $table = '';

        foreach($this->data as $row){
            $i = 0;
            foreach($row as $field){
                $len = $this->maxLength[$i] + 1; //ensures that the longest string has a space after it
                $table .= '| '.sprintf("%' -{$len}s",$field);
                $i++;
            }
            $table .= '|'.PHP_EOL;
        }

        $this->table .= $table;
        $this->table .= $this->line;
    }

    /**
     * Find maximum lengths for each column
     */
    private function getLengths(){

        for($i=0; $i<$this->columns; $i++){
            $this->maxLength[$i] = 0;

            //headers
            foreach($this->header as $field){

                //set initial max lengths to the length of each header cell
                if(strlen($field) > $this->maxLength[$i]){
                    $this->maxLength[$i] = strlen($field);
                }
            }
        }

            //data
        foreach($this->data as $row){
            //test each field in each row
            $i=0;
            foreach($row as $field){
                if(strlen($field) > $this->maxLength[$i]){
                    $this->maxLength[$i] = strlen($field);
                }
                $i++;
            }

        }

    }

    /**
    *   Checks that table header is an array
    */
    private function verifyHeader(){
        if(!is_array($this->header)){
            throw new \Exception('Table header must be an array');
        }
    }

    /**
    *  Verifies that data passed is an array and that it matches the array
    *   passed to header
    */
    private function verifyData(){
        if(!is_array($this->data)){
            throw new \Exception('Data passed must be an array');
        }

        if(!is_array($this->data[0])){
            throw new \Exception('Data must be an array of arrays');
        }

        if(count($this->data[0]) != $this->columns){
            throw new \Exception('Array length mismatch between table header and the data');
        }
    }
    
}
