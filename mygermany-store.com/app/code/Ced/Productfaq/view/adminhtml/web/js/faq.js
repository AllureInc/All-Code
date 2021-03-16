/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
    var scntDiv =  document.getElementById('p_scents');
    var i = document.querySelectorAll('#p_scents p').length+ 2;
function testing(e){  
    var scntDiv =  document.getElementById('p_scents');
    var html='<p class="del"><label>Title</label><label for="title"><input type="text" id="title[quest' + i +']title" size="20" name="title[quest' + i +']title" value="" placeholder="Input Value" /></label>  <label>Description</label> <label for="description"><textarea rows="4" id="description[desc'+i+']description" size="20" name="description[desc'+i+']description" column="4" placeholder="Please Enter Description"></textarea></label><a id="test2" href="javascript:;" onclick="check(event)">Remove</a></p>';
     
    scntDiv.insertAdjacentHTML('beforeend',html);
     i++;
     
}
function check(e){

       e.target.parentElement.remove();
    i--;
}


