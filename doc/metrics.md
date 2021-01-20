# List of available metrics

## Halstead complexity measures 
### Metrics: 
length, vocabulary, volume, difficulty, effort, level, bugs, time, intelligentContent, number_operators, number_operands, number_operators_unique, number_operands_unique    

### Description

**n1** = the number of distinct operators      
**n2** = the number of distinct operands      
**N1** = the total number of operators      
**N2** = the total number of operands      
    
From these numbers, eight measures can be calculated:      
    
**Program vocabulary:** n = n1 + n2      
**Program length:** N = N1 + N2      
**Calculated program length:** N' = n1 * log2(n1) + n2 * log2(n2)      
**Volume:** V = N * log2(n)        
**Difficulty:** D = (n1/2) * (N2/n2)        
**Effort:** E = D * V      
**Time required to program:** T = E / 18 seconds      
**Number of delivered bugs:** B = V / 3000

### Links     
[https://en.wikipedia.org/wiki/Halstead_complexity_measures](https://en.wikipedia.org/wiki/Halstead_complexity_measures)
[https://www.verifysoft.com/en_halstead_metrics.html](https://www.verifysoft.com/en_halstead_metrics.html)
    
## Cyclomatic complexity number and weighted method count 
### Metrics: 
wmc, ccn, ccnMethodMax
    
### Description
The cyclomatic complexity (CCN) is a measure of control structure complexity of a function or procedure.    
We can calculate ccn in two ways (we choose the second):    
    
1. Cyclomatic complexity (CCN) = E - N + 2P    
  Where:    
  P = number of disconnected parts of the flow graph (e.g. a calling program and a subroutine)    
  E = number of edges (transfers of control)    
  N = number of nodes (sequential group of statements containing only one transfer of control)    
    
2. CCN = Number of each decision point    
    
The weighted method count (WMC) is count of methods parameterized by a algorithm to compute the weight of a method.    
Given a weight metric w and methods m it can be computed as    
    
sum m(w') over (w' in w)    
    
Possible algorithms are:    
    
- Cyclomatic Complexity    
- Lines of Code    
- 1 (unweighted WMC)    
    
This visitor provides two metrics, the maximal CCN of all methods from one class (currently stored as ccnMethodMax)    
and the WMC using the CCN as weight metric (currently stored as ccn).    

### Links
[https://en.wikipedia.org/wiki/Cyclomatic_complexity](https://en.wikipedia.org/wiki/Cyclomatic_complexity)    
[http://www.literateprogramming.com/mccabe.pdf](http://www.literateprogramming.com/mccabe.pdf)    
[https://www.pitt.edu/~ckemerer/CK%20research%20papers/MetricForOOD_ChidamberKemerer94.pdf](https://www.pitt.edu/~ckemerer/CK%20research%20papers/MetricForOOD_ChidamberKemerer94.pdf)    
     
## Kan's defects 
### Metrics: 
kanDefect    

### Description
**kanDefect** = 0.15 + 0.23 *  number of do…while() + 0.22 *  number of switch() + 0.07 * number of if()    

### Links
    
## Maintainability Index 
### Metrics: 
mi, mIwoC, commentWeight    

### Description
    
According to Wikipedia, "Maintainability Index is a software metric which measures how maintainable (easy to support and change) the source code is. The maintainability index is calculated as a factored formula consisting  of Lines Of Code, Cyclomatic Complexity and Halstead volume."    
    
mIwoC: Maintainability Index without comments    
MIcw: Maintainability Index comment weight    
mi: Maintainability Index = MIwoc + MIcw    
    
**MIwoc** = 171 - 5.2 * ln(aveV) -0.23 * aveG -16.2 * ln(aveLOC)    
**MIcw** = 50 * sin(sqrt(2.4 * perCM))    
**mi** = MIwoc + MIcw    

### Links

[https://www.verifysoft.com/en_maintainability.html](https://www.verifysoft.com/en_maintainability.html)
 
    
## Lack of cohesion of methods 
### Metrics: 
lcom    
 
### Description   

Cohesion metrics measure how well the methods of a class are related to each other. A cohesive class performs one function while a non-cohesive class performs two or more unrelated functions. A non-cohesive class may need to be restructured into two or more smaller classes.    
High cohesion is desirable since it promotes encapsulation. As a drawback, a highly cohesive class has high coupling between the methods of the class, which in turn indicates high testing effort for that class.    
Low cohesion indicates inappropriate design and high complexity. It has also been found to indicate a high likelihood of errors. The class should probably be split into two or more smaller classes.    

### Links
[https://blog.ndepend.com/lack-of-cohesion-methods/](https://blog.ndepend.com/lack-of-cohesion-methods/)
[http://www.arisa.se/compendium/node116.html](http://www.arisa.se/compendium/node116.html)
    
## Card and Agresti metric 
### Metrics 
relativeStructuralComplexity, relativeDataComplexity, relativeSystemComplexity, totalStructuralComplexity, totalDataComplexity, totalSystemComplexity    
  
### Description 
Fan-out = Structural fan-out = Number of other procedures this procedure calls    
    
v = number of input/output variables for a procedure    
    
(SC) Structural complexity = fan-out^2    
(DC) Data complexity = v / (fan-out + 1)  
  
### Links  
[https://www.witpress.com/Secure/elibrary/papers/SQM94/SQM94024FU.pdf](https://www.witpress.com/Secure/elibrary/papers/SQM94/SQM94024FU.pdf)

## Length 
### Metrics: 
cloc, loc, lloc    

### Description

**loc:** lines count      
**cloc:** lines count without multiline comments    
**lloc:** lines count without empty lines    

### Links
    
## Methods 
### Metrics: 
nbMethodsIncludingGettersSetters, nbMethods, nbMethodsPrivate, nbMethodsPublic, nbMethodsGetter, nbMethodsSetters    
    
## Coupling 
### Metrics: 
afferentCoupling, efferentCoupling, instability    

### Description
    
**Afferent couplings (Ca):** The number of classes in other packages that depend upon classes within the package is an indicator of the package's responsibility.    
**Efferent couplings (Ce):** The number of classes in other packages that the classes in a package depend upon is an indicator of the package's dependence on externalities.    
**Instability (I):** The ratio of efferent coupling (Ce) to total coupling (Ce + Ca) such that I = Ce / (Ce + Ca).    

### Links
[https://www.future-processing.pl/blog/object-oriented-metrics-by-robert-martin/](https://www.future-processing.pl/blog/object-oriented-metrics-by-robert-martin/)
[https://en.wikipedia.org/wiki/Software_package_metrics](https://en.wikipedia.org/wiki/Software_package_metrics)
    
## Depth of inheritance tree 
### Metrics: 
depthOfInheritanceTree    

### Description
Measures the length of inheritance from a class up to the root class.

### Links    
    
## Page rank 
### Metrics: 
pageRank

### Description

### Links
