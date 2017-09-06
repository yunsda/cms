<?php
namespace taglib;

use \think\template\TagLib;

class FragmentTag extends TagLib
{

    protected $tags = array(
        'input' => array(
            'attr' => 'id,name,hidden,readonly,onclick,style',
            'close' => 0
        ),
        'select' => array(
            'attr' => 'id,name,hidden,style',
            'close' => 1
        )
    );

    /**
     * 用法
     * {FragmentTag:input id='sdf' name=''/}
     * 
     * @param
     *            $tag
     * @param
     *            $content
     * @return string @autor: guiyj
     *        
     *         表单中input框标签中各个参数的说明:
     *         hidden : input hidden框的id
     *         id : input框id
     *         name : input框名称
     *         readonly : readonly属性
     *         clickFun : 点击事件的方法名
     *         style : 附加的css属性
     */
    public function tagInput($tag, $content)
    {
        $return = "<div class='form-group'>
            <label class='col-sm-3 control-label'>{$tag['name']}</label>
            <div class='col-sm-9'>
            <input class='form-control' id='{$tag['id']}' name='{$tag['id']}' ";
        if (! empty($tag['value'])) {
            if (false === strpos($tag['value'], '$')) {
                $return .= "value='" . $tag['value'] . "' ";
            } else {
                $return .= "value='<?php echo " . $tag['value'] . "?>' ";
            }
        }
        if (! empty($tag['type'])) {
            $return .= 'type="' . $tag['type'] . '"';
        } else {
            $return .= 'type="text"';
        }
        if (! empty($tag['readonly'])) {
            $return .= 'readonly="' . $tag['readonly'] . '"';
        }
        if (! empty($tag['onclick'])) {
            $return .= ' onclick="' . $tag['onclick'] . '" ';
        }
        if (! empty($tag['style'])) {
            $return .= 'style="' . $tag['style'] . '"';
        }
        if (! empty($tag['disabled'])) {
            $return .= 'disabled="' . $tag['disabled'] . '"';
        }
        $return .= '>';
        
        if (! empty($tag['hidden'])) {
            $return .= '<input class="form-control" type="hidden" id="' . $tag['hidden'] . '" value="' . $tag['hiddenValue'] . '">';
        }
        if (! empty($tag['selectFlag'])) {
            $return .= '<input class="form-control" type="hidden" id="' . $tag['hidden'] . '" value="' . $tag['hiddenValue'] . '">';
        }
        
        $return .= '</div> </div>';
        if (! empty($tag['underline']) && 'true' == $tag['underline']) {
            $return .= '<div class="hr-line-dashed"></div>';
        }
        return $return;
    }

    /**
     * 用法
     * {select id="sex" name="性别" underline="true"}
						<option value="1">男</option>
						<option value="2">女</option>
					{/select}
     * 
     * @param
     *            $tag
     * @param
     *            $content
     * @return string @autor: guiyj
     *        
            select标签中各个参数的说明:
            name : select的名称
            id : select的id
            underline : 是否带分割线
     */
    public function tagSelect($tag, $content)
    {
        $return = "<div class='form-group'>
            <label class='col-sm-3 control-label'>{$tag['name']}</label>
            <div class='col-sm-9'>
            <select class='form-control' id='{$tag['id']}' name='{$tag['id']}'>
        ";
        $return .= $content;
        $return .= '</select>';
        
        if (! empty($tag['hidden'])) {
            $return .= '<input class="form-control" type="hidden" id="' . $tag['hidden'] . '" value="' . $tag['hiddenValue'] . '">';
        }
        $return .= '</div></div>';
        
        if (! empty($tag['underline']) && 'true' == $tag['underline']) {
            $return .= '<div class="hr-line-dashed"></div>';
        }
        return $return;
    }
}
?>