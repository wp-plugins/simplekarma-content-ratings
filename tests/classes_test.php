<?php
/*
This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/ 
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');
require_once("../../../../wp-config.php");
require_once('../classes/classes.php');

Mock::generate('wpdb','Mockwpdb');

class Karma
{
    function Karma($masterId, $prefix, $karma)
	{
		global $wpdb;
	    $this->id = $id;
	    $this->prefix = $prefix;
	    $this->karma = $karma;
	}
}

$mock_location = array(
    new Karma(1, 'bar', 1),
    new Karma(2, 'bar', 0),
    new Karma(3, 'bar', -1),
	);

class TestOfSimpleKarma extends UnitTestCase
{
    function TestOfSimpleKarma()
	{
        $this->UnitTestCase('SimpleKarma class');
    }
	
	function test_ObjectInit()
	{
        $obj = new SimpleKarma('test-karma');
        $this->assertNotNull($obj);
		$this->assertEqual($obj->table, 'test-karma', 'Karma prefix supplied to constructor does not match return from getPrefix()');
    }
	
	function test_modifyKarmaAdd()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->modifyKarma("1", 1);
		$this->assertEqual($results, 2);
	}
	
	function test_modifyKarmaSubtract()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->modifyKarma("1", -1);
		$this->assertEqual($results, 0);
	}
	
	function test_getKarmaWidget()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $result = $obj->getKarmaWidget('1');
		$this->assertPattern("/<!-- SimpleKarma Widget Version 0.1 -->/", $result, "Begin Karma Widget comment is missing or incorrect");
		$this->assertPattern("/<div class='simplekarma-widget'>/", $result, "The widget div class is missing or incorrect ");
		//Up Button
		$this->assertPattern("/<img id=\"up-1\"/", $result, "The image id is missing or incorrect");
		$this->assertPattern("/images\/up.gif/", $result, "The image path is missing or incorrect");
		$this->assertPattern("/alt=\"Add karma\"/", $result, "The alt tag is missing or incorrect");
		$this->assertPattern("/javascript\:modifyKarma/", $result, "The incorrect javascript is being called");
		$this->assertPattern("/'1',1, 'add'/", $result, "Incorrect parameters are being sent to the javascript function");
		$this->assertPattern("/'bar'/", $result, "Incorrect prefix is being sent to the javascript function");
		//Down Button
		$this->assertPattern("/<img id=\"down-1\"/", $result, "The image id is missing or incorrect");
		$this->assertPattern("/images\/down.gif/", $result, "The image path is missing or incorrect");
		$this->assertPattern("/alt=\"Subtract karma\"/", $result, "The alt tag is missing or incorrect");
		$this->assertPattern("/javascript\:modifyKarma/", $result, "The incorrect javascript is being called");
		$this->assertPattern("/'1',1, 'subtract'/", $result, "Incorrect parameters are being sent to the javascript function");
		$this->assertPattern("/'bar'/", $result, "Incorrect prefix is being sent to the javascript function");
		//Karma
		$this->assertPattern("/\<small id=\"karma-1\"\>1\<\/small\>/", $result, "Karma is missing or incorrect");
		$this->assertPattern("<!-- End SimpleKarma Widget -->", $result , "End karma comment is missing or incorrect");
	}
	
	function test_getAdminKarmaWidget()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $result = $obj->getAdminKarmaWidget('1');
		$this->assertPattern("/<!-- SimpleKarma Admin Widget Version 0.1 -->/", $result, "Begin Karma Widget comment is missing or incorrect");
		$this->assertPattern("/<div class='simplekarma-widget'>/", $result, "The widget div class is missing or incorrect ");
		//Up Button
		$this->assertPattern("/<img id=\"up-1\"/", $result, "The image id is missing or incorrect");
		$this->assertPattern("/images\/up.gif/", $result, "The image path is missing or incorrect");
		$this->assertPattern("/alt=\"Add karma\"/", $result, "The alt tag is missing or incorrect");
		$this->assertPattern("/javascript\:modifyKarma/", $result, "The incorrect javascript is being called");
		$this->assertPattern("/'1',1, 'add'/", $result, "Incorrect parameters are being sent to the javascript function");
		$this->assertPattern("/'bar'/", $result, "Incorrect prefix is being sent to the javascript function");
		//Down Button
		$this->assertPattern("/<img id=\"down-1\"/", $result, "The image id is missing or incorrect");
		$this->assertPattern("/images\/down.gif/", $result, "The image path is missing or incorrect");
		$this->assertPattern("/alt=\"Subtract karma\"/", $result, "The alt tag is missing or incorrect");
		$this->assertPattern("/javascript\:modifyKarma/", $result, "The incorrect javascript is being called");
		$this->assertPattern("/'1',1, 'subtract'/", $result, "Incorrect parameters are being sent to the javascript function");
		$this->assertPattern("/'bar'/", $result, "Incorrect prefix is being sent to the javascript function");
		//Karma
		$this->assertPattern("/\<small id=\"karma-1\"\>1\<\/small\>/", $result, "Karma is missing or incorrect");
		$this->assertPattern("<!-- End SimpleKarma Admin Widget -->", $result , "End karma comment is missing or incorrect");
	}
	
	function test_getAbuseWidget()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $result = $obj->getAbuseWidget('1');
		$this->assertPattern("/<!-- SimpleKarma Abuse Widget Version 0.1 -->/", $result, "Begin Karma Widget comment is missing or incorrect");
		$this->assertPattern("/<div class='simplekarma-widget'>/", $result, "The widget div class is missing or incorrect ");
		//Down Button
		$this->assertPattern("/<img id=\"down-1\"/", $result, "The image id is missing or incorrect");
		$this->assertPattern("/images\/inappropriate-active-icon.gif/", $result, "The image path is missing or incorrect");
		$this->assertPattern("/title=\'Flag this post as inappropriate\'/", $result, "The alt tag is missing or incorrect");
		$this->assertPattern("/javascript\:modifyKarma/", $result, "The incorrect javascript is being called");
		$this->assertPattern("/'1',1, 'subtract'/", $result, "Incorrect parameters are being sent to the javascript function");
		$this->assertPattern("/'bar'/", $result, "Incorrect prefix is being sent to the javascript function");
		//Karma
		$this->assertPattern("/\<small id=\"karma-1\"\>1\<\/small\>/", $result, "Karma is missing or incorrect");
		$this->assertPattern("<!-- End SimpleKarma Abuse Widget -->", $result , "End karma comment is missing or incorrect");
	}
	
	function test_getForeignTables()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->getForeignTables();
		$this->assertEqual($results[0]->prefix, 'bar');
	}
	
	function test_isAboveThreshold()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->isAtThreshold(1);
		$this->assertEqual($results, false);
	}
	
	function test_hasPassedThreshold()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->isFlagged(1);
		$this->assertEqual($results, false);
	}
	
	function test_deleteComment()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->deleteComment('1');
		$this->assertEqual($results, "delete from bar where id=1");
	}
	
	function test_getHighRateObjects()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->getHighRateObjects();
		$this->assertEqual($results[0]->karma, 1);
		$this->assertEqual($results[1]->karma, 0);
		$this->assertEqual($results[2]->karma, -1);
	}
	
	function test_getFlaggedObjects()
	{
	    global $mock_location;
	    $mockWpdb = &new Mockwpdb();
		$mockWpdb->setReturnValue('get_results', $mock_location);
	    $obj = new SimpleKarma('bar',$mockWpdb);
	    $results = $obj->getFlaggedObjects();
		$this->assertEqual($results[0]->karma, 1);
	}
	
	function testForMultipleRecords()
	{
		$obj = new SimpleKarma('test');
		$obj->modifyKarma(99999999, 1);
		$obj->modifyKarma(99999999, -1);
		$obj->modifyKarma(99999999, -1);
		$query = "select karma from wp_simple_karma where foreign_table='test' and object_id=99999999"; 
		$results = $obj->db->get_results($query);
		$query = "delete from wp_simple_karma where foreign_table='test' and object_id=99999999";
		$obj->db->query($query);
		$this->assertEqual($results[0]->karma, -1);
		
	}

		
}

$test = &new TestOfSimpleKarma();
$test->run(new HtmlReporter());

?>
