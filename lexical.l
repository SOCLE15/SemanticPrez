%{
	#include <stdlib.h>
	int nmbNonMatch = 0;
	int nmbLine = 0;
%}
WORD [a-zA-Z][a-zA-Z0-9]*
NUMBER [0-9]+
SPACE [ \t]+
%x COMMENT
%%
"/*" {BEGIN COMMENT;}
<COMMENT>[^*\n]* {printf("commentaire debut\n");}
<COMMENT>"*"+[^*/\n]* {}
<COMMENT>"*"+"/" {BEGIN INITIAL; printf("commentaire fin\n");} 
{SPACE}		{}
\n 			{printf("\n");nmbLine++;}
void		{printf("tVOID");}
"("			{printf("tPAROP");}
")"			{printf("tPARCL");}
labels 		{printf("tLABELS");}
vars		{printf("tVARS");}
functions	{printf("tFUNCTIONS");}
:			{printf("tDOTS");}
;			{printf("tPV");}
,			{printf("tVIRG");}
"{"			{printf("tCROP");}
"}"			{printf("tCRCL");}
goto		{printf("tGOTO");}
int 		{printf("tINT");}
if			{printf("tIF");}
{WORD}		{printf("tWORD : %s",strdup(yytext));}
{NUMBER}	{printf("tNUMBER : %d", atoi(yytext));}
. 			{printf("NNMATCH : %s", yytext);nmbNonMatch++;}


%%
int main(void) {
	yylex();
	printf("\n Nombre non matché : %d\n", nmbNonMatch);
	}

