<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the project task.
 *
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucProjectTaskTest extends phpucAbstractTest
{
    /**
     * A prepared console arg object.
     * 
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $args
     */
    protected $args = null;
    
    /**
     * Creates a prepared {@link phpucConsoleArgs} instance and the required
     * /projects directory.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->prepareArgv( array(
            'example',
            '--project-name',
            'phpUnderControl',
            PHPUC_TEST_DIR
        ) );
        
        $this->args = new phpucConsoleArgs();
        $this->args->parse();
        
        $this->createTestDirectories(
            array(
                '/projects',
                '/apache-ant-1.7.0'
            )
        );
        
        $this->createTestFile( '/config.xml', '<cruisecontrol />' );
    }
    
    /**
     * This test should run without any error.
     *
     * @return void
     */
    public function testValidateProjectTaskNoError()
    {
        $task = new phpucProjectTask( $this->args );
        $task->validate();
    }
    
    /**
     * Tests that the {@link phpucProjectTask::validate()} method fails with an
     * exception if no /projects directory exists.
     *
     * @return void
     */
    public function testValidateProjectTaskWithoutCCProjectsDirFail()
    {
        rmdir( PHPUC_TEST_DIR . '/projects' );
        
        $task = new phpucProjectTask( $this->args );
        try
        {
            $task->validate();
            $this->fail( 'phpucValidateException expected.' );
        }
        catch ( phpucValidateException $e ) {}
    }
    
    /**
     * Tests that the {@link phpucProjectTask::validate()} method fails with an
     * exception if a project with the same name exists.
     *
     * @return void
     */
    public function testValidateProjectTaskWithExistingProjectDirectoryFail()
    {
        $this->createTestDirectories( array( '/projects/phpUnderControl' ) );
        
        $task = new phpucProjectTask( $this->args );
        try
        {
            $task->validate();
            $this->fail( 'phpucValidateException expected.' );
        }
        catch ( phpucValidateException $e ) {}
    }
    
    /**
     * Tests the {@link phpucProjectTask::execute()} method which should not fail
     * and which should create some files and directories.
     *
     * @return void
     */
    public function testExecuteProjectTaskNoError()
    {
        $task = new phpucProjectTask( $this->args );
        
        ob_start();
        $task->execute();
        ob_end_clean();
        
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/source' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build/logs' );
        $this->assertFileExists( PHPUC_TEST_DIR . '/projects/phpUnderControl/build.xml' );
        
        $sxml = simplexml_load_file( PHPUC_TEST_DIR . '/config.xml' );
        $this->assertEquals( 1, count( $sxml->xpath( '//project[@name="phpUnderControl"]' ) ) );
        
        $sxml = simplexml_load_file( PHPUC_TEST_DIR . '/projects/phpUnderControl/build.xml' );
        $this->assertEquals( 'phpUnderControl', (string) $sxml['name'] );
    }
    
    /**
     * Tests that the {@link phpucProjectTask::execute()} method fails with an
     * exception if no ant directory exists.
     *
     * @return void
     */
    public function testExecuteProjectTaskWithoutAntDirectoryFail()
    {
        rmdir( PHPUC_TEST_DIR . '/apache-ant-1.7.0' );
        
        $task = new phpucProjectTask( $this->args );
        ob_start();
        try
        {
            $task->execute();
            $this->fail( 'phpucExecuteException expected.' );
        }
        catch ( phpucExecuteException $e ) {}
        ob_end_clean();
    }
}