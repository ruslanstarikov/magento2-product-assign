OVERVIEW

    This module assign and delete products by sku in the given categories. It was made it especially to save time when we needed to move products
    from one category to another one on scanpan, which use configurable products. 
Example
  

Technical:
    Reason why I didn't abstract 'Run' Controller together, it was because I choose flexibility instead abstraction. 
     
Versions:
    
    1.0.0: initial module
    
    1.0.1: it has re-written the entire insert functionality avoiding use Magento assignProduct 
        which it was deleting the previous association.
    
