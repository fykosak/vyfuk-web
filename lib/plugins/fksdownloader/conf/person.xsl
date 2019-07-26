<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>
    <xsl:template match="/">        
        <xsl:for-each  select="//data/row">                    
            <span class="person">
                <xsl:value-of select="name"/>
            </span>
            <xsl:if test="position() &lt; last()" >, </xsl:if>
        </xsl:for-each>        
    </xsl:template>
</xsl:stylesheet>
