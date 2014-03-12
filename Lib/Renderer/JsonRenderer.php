<?php
class JsonRenderer {
    public function __contruct(View $View) {
        $this->View = $View;
    }
    public function render($views, $vars = array()) {
        $this->View->response->type('json');
        $this->View->getEventManager()->dispatch(new CakeEvent('View.beforeRender', $this->View, array(join(' ', $view))));
        $vars += $this->setTemplates();
        $return = $this->serialize($vars);
        if (!empty($vars['_jsonp'])) {
            $jsonp = $vars['_jsonp'];
            if ($jsonp === true) {
                $jsonp = 'callback';
            }
            if (isset($this->request->query[$jsonp])) {
                $return = sprintf('%s(%s)', h($this->request->query[$jsonp]), $return);
                $this->response->type('js');
            }
        }
        $this->View->getEventManager()->dispatch(new CakeEvent('View.afterRender', $this->View, array(join(' ', $view))));
        return $return;
    }
    public function serialize($vars) {
        if (!empty($vars['_serialize'])) {
            $keys = $vars['_serialize'];
            if (is_array($keys)) {
                $data = array();
                foreach ($keys as $alias => $key) {
                    if (is_numeric($alias)) {
                        $alias = $key;
                    }
                    if (array_key_exists($key, $vars)) {
                        $data[$alias] = $vars[$key];
                    }
                }
                $data = !empty($data) ? $data : null;
            } else {
                $data = isset($vars[$keys]) ? $vars[$keys] : null;
            }
            if (is_null($data)) {
                $data = new StdClass;
            }
        } else {
            $data = $vars;
        }
        
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && Configure::read('debug')) {
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        return json_encode($data);
    }

    private function setTemplates() {
        if ($this->View->request->is('ajax') && !empty($this->View->request->query['templates'])) {
            App::uses('MustacheRenderer', 'PvtPlugin.Lib.Renderer');
            $MustacheRenderer = new MustacheRenderer($this->View);
            foreach ($views as $v) {
                $MustacheRenderer->compile($v, array());
            }
            return array('templates' => $MustacheRenderer->getTemplates());
        }
        return array();
    }
}