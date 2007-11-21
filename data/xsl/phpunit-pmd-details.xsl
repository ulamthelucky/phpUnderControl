<?xml version="1.0"?>
<!--********************************************************************************
 * CruiseControl, a Continuous Integration Toolkit
 * Copyright (c) 2001, ThoughtWorks, Inc.
 * 200 E. Randolph, 25th Floor
 * Chicago, IL 60601 USA
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     + Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *
 *     + Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials provided
 *       with the distribution.
 *
 *     + Neither the name of ThoughtWorks, Inc., CruiseControl, nor the
 *       names of its contributors may be used to endorse or promote
 *       products derived from this software without specific prior
 *       written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html"/>
  <xsl:param name="viewcvs.url"/>
  <xsl:variable name="project" select="/cruisecontrol/info/property[@name='projectname']/@value"/>
  <xsl:param name="cvsmodule" select="concat($project, '/source/src/')"/>
  <xsl:key name="rules" match="violation" use="@rule"/>

  <xsl:include href="./phphelper.xsl" />

  <xsl:template match="/">
    <xsl:apply-templates select="cruisecontrol/pmd"/>
  </xsl:template>

  <xsl:template match="pmd">
    <xsl:variable name="total.error.count" select="count(file/violation)" />
    <xsl:apply-templates select="." mode="summary"/>
    <xsl:apply-templates select="." mode="rule-summary"/>
    
    <table class="result" align="center">
      <colgroup>
        <col width="5%"></col>
        <col width="5%"></col>
        <col width="85%"></col>
        <col width="5%"></col>
      </colgroup>
      <xsl:for-each select="file[violation]">
        <xsl:sort data-type="number" order="descending" select="count(violation)"/>
        <xsl:apply-templates select="."/>
      </xsl:for-each>
    </table>

    <xsl:apply-templates select="//pmd-cpd/duplication" />
  </xsl:template>

  <xsl:template match="pmd" mode="summary">
    <table align="center" cellpadding="2" cellspacing="0" border="0" width="98%">
      <tr>
        <td class="header-title">PHPUnit PMD Summary</td>
      </tr>
      <tr>
        <td class="header-data">
          <span class="header-label">Files:&#160;</span>
          <xsl:value-of select="count(file[violation]) + count(//pmd-cpd/duplication/file)"/>
        </td>
      </tr>
      <tr>
        <td class="header-data">
          <span class="header-label">Violations:&#160;</span>
          <xsl:value-of select="count(file/violation) + count(//pmd-cpd/duplication)"/>
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="pmd" mode="rule-summary">
    <p/>
    <table class="result" align="center">
      <colgroup>
        <col width="5%"></col>
        <col width="85%"></col>
        <col width="5%"></col>
        <col width="3%"></col>
      </colgroup>
      <thead>
        <tr>
          <th></th>
          <th>PHPUnit PMD rule</th>
          <th>Files</th>
          <th>Error/Warnings</th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="file/violation[generate-id() = generate-id(key('rules', @rule)[1])]">
          <xsl:sort data-type="number" order="descending" select="count(key('rules', @rule))"/>

          <xsl:variable name="errorCount" select="count(key('rules', @rule))"/>
          <xsl:variable name="fileCount" select="count(../../file[violation/@rule=current()/@rule])"/>
          <tr>
            <xsl:if test="position() mod 2 = 0">
              <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td></td>
            <td class="checkstyle-data"><xsl:value-of select="@ruleset"/> / <xsl:value-of select="@rule"/></td>
            <td class="checkstyle-data" align="right"><xsl:value-of select="$fileCount"/></td>
            <td class="checkstyle-data" align="right"><xsl:value-of select="$errorCount"/></td>
          </tr>
        </xsl:for-each>
        <xsl:if test="count(//pmd-cpd/duplication) &gt; 0">
          <xsl:variable name="duplication.count" select="count(//pmd-cpd/duplication)" />
          <xsl:variable name="duplication.file.count" select="count(//pmd-cpd/duplication/file)" />
          <tr>
            <xsl:if test="count(file/violation) mod 2 != 0">
              <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td></td>
            <td class="checkstyle-data"> / CopyPasteDetection</td>
            <td class="checkstyle-data" align="right"><xsl:value-of select="$duplication.file.count" /></td>
            <td class="checkstyle-data" align="right"><xsl:value-of select="$duplication.count" /></td>
          </tr>
        </xsl:if>
      </tbody>
    </table>
  </xsl:template>

  <xsl:template match="duplication">
    <table class="result" align="center">
      <colgroup>
        <col width="5%"/>
        <col width="5%"/>
        <col width="85%"/>
        <col width="5%"/>
      </colgroup>
      <thead>
        <tr><td colspan="4"><br/></td></tr>
        <tr>
          <th colspan="4">Duplication
          (Files: <xsl:value-of select="count(file)" />,
           Lines: <xsl:value-of select="@lines" />,
           Tokens: <xsl:value-of select="@tokens" />)</th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="file">
          <tr>
            <xsl:if test="position() mod 2 = 0">
              <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td/>
            <td align="right" class="warning"><xsl:value-of select="@line" /></td>
            <td class="warning"><xsl:value-of select="@path" /></td>
            <td class="warning"/>
          </tr>
        </xsl:for-each>
        <tr>
          <td colspan="2"> </td>
          <td colspan="2">
            <pre class="code-fragment">
              <xsl:text>    </xsl:text>
              <xsl:value-of select="codefragment/text()" />
            </pre>
          </td>
        </tr>
      </tbody>
    </table>

  </xsl:template>

  <xsl:template match="file">
    <xsl:variable name="javaclass">
      <xsl:call-template name="phpname">
        <xsl:with-param name="filename" select="@name"/>
      </xsl:call-template>
    </xsl:variable>
    <xsl:variable name="filename" select="translate(@name,'\','/')"/>
    <thead>
      <tr><td colspan="4"><br/></td></tr>
      <tr>
        <th colspan="4">
          <xsl:value-of select="$javaclass"/>
          (<xsl:value-of select="count(violation)"/>)
        </th>
      </tr>
    </thead>
    <tbody>
      <xsl:for-each select="violation">
        <xsl:variable name="style">
          <xsl:choose>
            <xsl:when test="@priority &lt;= 2">error</xsl:when>
            <xsl:otherwise>warning</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <tr>
          <xsl:if test="position() mod 2 = 0">
            <xsl:attribute name="class">oddrow</xsl:attribute>
          </xsl:if>
          <td />
          <td class="{$style}" align="right">
            <xsl:call-template name="viewcvs">
              <xsl:with-param name="file" select="$filename"/>
              <xsl:with-param name="line" select="@line"/>
            </xsl:call-template>
          </td>
          <td class="{$style}">
            <xsl:value-of disable-output-escaping="no" select="."/>
          </td>
          <td class="{$style}">
            <xsl:value-of select="@priority"/>
          </td>
        </tr>
      </xsl:for-each> 
    </tbody>
  </xsl:template>

    <xsl:template name="viewcvs">
      <xsl:param name="file"/>
      <xsl:param name="line"/>
      <xsl:choose>
        <xsl:when test="not($viewcvs.url)">
          <xsl:value-of select="$line"/>
        </xsl:when>
        <xsl:otherwise>
          <a>
            <xsl:attribute name="href">
              <xsl:value-of select="concat($viewcvs.url, $cvsmodule)"/>
              <xsl:value-of select="substring-after($file, $cvsmodule)"/>
              <xsl:text>?annotate=HEAD#</xsl:text>
              <xsl:value-of select="$line"/>
            </xsl:attribute>
            <xsl:value-of select="$line"/>
          </a>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
