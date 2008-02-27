<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the console input class.
 *
 * @category  QualityAssurance
 * @package   Console
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConsoleInputTest extends phpucAbstractTest
{
    /**
     * Tests that {@link phpucConsoleInput::parse()} fails with an exception if
     * a mandatory option without a default default is not defined on the cli.
     *
     * @return void
     */
    public function testConsoleInputWithoutMandatoryOptionAndNoDefaultValueFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'foo', 'The foo command.' );
        $definition->addOption( 'foo', 'b', 'bar', 'The bar option', true, null, true );
        
        $this->prepareArgv( array( 'foo' ) );
        
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--bar' is marked as mandatory and not set."
        );
        
        $input = new phpucConsoleInput( $definition );
        $input->parse();
    }

    /**
     * Tests that {@link phpucConsoleInput::parse()} fails with an exception if
     * a mandatory option without a default default is not defined on the cli.
     *
     * @return void
     */
    public function testConsoleInputWithoutMandatoryOptionAndNoDefaultValueButFollowingOptionFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'foo', 'The foo command.' );
        $definition->addOption( 'foo', 'b', 'bar', 'The bar option', true, null, true );
        
        $this->prepareArgv( array( 'foo', '-b', '-a' ) );
        
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '-b' requires an additional value."
        );
        
        $input = new phpucConsoleInput( $definition );
        $input->parse();
    }
    
    /**
     * Tests that {@link phpucConsoleInput::parse()} fails with an exception if
     * a whitelisted option is not in the whitelist.
     *
     * @return void
     */
    public function testConsoleInputWithInvalidWhitelistOptionValueFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'foo', 'The foo command.' );
        $definition->addOption( 
            'foo', 'b', 'bar', 'The bar option', array( 'a', 'b' ), null, true
        );
        
        $this->prepareArgv( array( 'foo', '--bar', 'c' ) );
        
        $this->setExpectedException(
            'phpucConsoleException',
            'The value for option --bar must match one of these values "a", "b".'
        );
        
        $input = new phpucConsoleInput( $definition );
        $input->parse();
    }
    
    /**
     * Tests that {@link phpucConsoleInput::parse()} fails with an exception if
     * an option value doesn't match against the defined regexp.
     *
     * @return void
     */
    public function testConsoleInputWithInvalidFormatedOptionValueFail()
    {
        $definition = new phpucConsoleInputDefinition();
        $definition->addCommand( 'foo', 'The foo command.' );
        $definition->addOption( 
            'foo', 'b', 'bar', 'The bar option', '/^[0-9a-f]{4}\-[0-9a-f]{2}$/D', null, true
        );
        
        $this->prepareArgv( array( 'foo', '--bar', '071a-0' ) );
        
        $this->setExpectedException(
            'phpucConsoleException',
            "The value for option '--bar' has an invalid format."
        );
        
        $input = new phpucConsoleInput( $definition );
        $input->parse();
    }
}