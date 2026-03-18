<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Builder_page;
use App\Models\FileUploader;
use App\Models\FrontendSetting;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    public function page_list()
    {
        return view('admin.page_builder.page_list');
    }

    public function page_store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Builder_page::insert(['name' => $request->name, 'created_at' => date('Y-m-d H:i:s')]);
        return redirect(route('admin.pages'))->with('success', get_phrase('New home page layout has been added'));
    }

    public function page_update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        Builder_page::where('id', $id)->update(['name' => $request->name, 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect(route('admin.pages'))->with('success', get_phrase('Home page name has been updated'));
    }

    public function page_delete($id)
    {
        Builder_page::where('id', $id)->delete();
        return redirect(route('admin.pages'))->with('success', get_phrase('The page name has been updated'));
    }

    public function page_status($id)
    {
        $query = Builder_page::where('id', $id);
        if ($query->first()->status == 1) {
            $query->update(['status' => 0]);
            $response = [
                'success' => get_phrase('Home page deactivated'),
            ];
        } else {
            FrontendSetting::where('key', 'home_page')->update(['value' => $query->first()->identifier]);
            $query->update(['status' => 1]);
            $response = [
                'success' => get_phrase('Home page activated'),
            ];
        }
        Builder_page::where('id', '!=', $id)->update(['status' => 0]);

        return json_encode($response);
    }

    public function page_layout_edit($id)
    {
        return view('admin.page_builder.page_layout_edit', ['id' => $id]);
    }

    // function page_layout_update(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         // 'developer_elements' => 'required',
    //         'builder_elements' => 'required',
    //     ]);

    //     // $get_developer_elements = $request->developer_elements;
    //     return $get_developer_elements = $this->developer_file_elements();
    //     $post_builder_elements = $request->builder_elements;

    //     $built_file_names = [];
    //     foreach ($post_builder_elements as $file_name => $builder_elements) {
    //         $arr_values = array_values($builder_elements);
    //         if ($arr_values[0]['tag'] == 'null') continue;

    //         $built_file_names[] = $file_name;
    //         $developer_file_content = file_get_contents(base_path('resources/views/components/home_made_by_developer/' . $file_name . '.blade.php'));
    //         $developer_file_content = $this->replace_special_character($developer_file_content);
    //         foreach ($builder_elements as $key => $builder_element) {
    //             $developer_element = $get_developer_elements[$file_name][$key];

    //             if ($this->decodeContent($builder_element['tag']) == 'img') {
    //                 $builder_element_src = explode('/public/', $this->decodeContent($builder_element['src']));

    //                 if (array_key_exists(1, $builder_element_src)) {
    //                     $updated_url_from_builder = "{{asset('" . $builder_element_src[1] . "')}}";
    //                 } else {
    //                     $updated_url_from_builder = "{{asset('" . $this->decodeContent($builder_element['src']) . "')}}";
    //                 }

    //                 $prepared_single_element = str_replace($developer_element['src'], $updated_url_from_builder, $developer_element['element']);
    //                 $developer_file_content = str_replace($developer_element['element'], $prepared_single_element, $developer_file_content);
    //             } elseif ($this->decodeContent($builder_element['tag']) != 'null') {
    //                 $developer_element_tag = $this->replace_special_character($developer_element['element']);
    //                 $developer_element_content = $this->replace_special_character($developer_element['content']);

    //                 $prepared_single_element = str_replace($developer_element_content, '{{ get_phrase("' . $this->decodeContent($builder_element['content']) . '") }}', $developer_element_tag);
    //                 $prepared_single_element = str_replace('(""', '("', $prepared_single_element);
    //                 $prepared_single_element = str_replace('"")', '")', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase(") }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase() }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase("") }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase(")}}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase()}}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase("")}}', '', $prepared_single_element);
    //                 $developer_file_content = str_replace($developer_element_tag, $prepared_single_element, $developer_file_content);
    //             }
    //         }

    //         $developer_file_content = str_replace('{{ get_phrase(") }}', '', $developer_file_content);
    //         $developer_file_content = str_replace('{{get_phrase(")}}', '', $developer_file_content);

    //         file_put_contents(base_path("resources/views/components/home_made_by_builder/" . $file_name . '.blade.php'), $developer_file_content);
    //     }
    //     Builder_page::where('id', $id)->update(['html' => json_encode($built_file_names)]);
    //     return redirect(route('admin.pages'))->with('success', get_phrase('Page layout has been updated'));
    // }

    public function page_layout_update(Request $request, $id)
    {
        $validated = $request->validate([
            'builder_elements' => 'required',
        ]);

        // $get_developer_elements = $this->developer_file_elements();
        $post_builder_elements = $request->builder_elements;

        // dd($post_builder_elements);

        $built_file_names = [];

        foreach ($post_builder_elements as $file_name => $builder_elements) {
            // Skip invalid blocks
            // $arr_values = array_values($builder_elements);
            // if ($arr_values[0]['tag'] == 'null') continue;

            $built_file_names[] = $file_name;

            // Load original developer section
            $file_path           = base_path('resources/views/components/home_made_by_developer/' . $file_name . '.blade.php');
            $developer_html      = file_get_contents($file_path);
            $developer_html_safe = preg_replace_callback('/{{(.*?)}}/s', function ($matches) {
                return '__BLADE_OPEN__' . base64_encode($matches[1]) . '__BLADE_CLOSE__';
            }, $developer_html);

            // ✅ Wrap in dummy container to prevent <html><body><p> wrapping
            $wrapped_html = '<div id="__temp_wrapper__">' . $developer_html_safe . '</div>';

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($wrapped_html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath     = new \DOMXPath($dom);
            $container = $dom->getElementById('__temp_wrapper__');

            // // 🔹 Sort builder elements by selector (keep DOM order)
            // usort($builder_elements, function ($a, $b) {
            //     return strcmp($a['selector'], $b['selector']);
            // });

            foreach ($builder_elements as $builder_element) {
                $identity = $builder_element['identity'];

                $tag     = $this->decodeContent($builder_element['tag']);
                $content = $this->decodeContent($builder_element['content']);
                $src     = $this->decodeContent($builder_element['src']);
                $element = $this->decodeContent($builder_element['element']);

                // ✅ Safe fallback support for new + old format
                $dropAreaIndex = $builder_element['dropAreaIndex'] ?? null;
                $droppedIndex  = $builder_element['droppedIndex'] ?? null;

                // New nested (hierarchical) structure
                $dropAreaPath = $builder_element['dropAreaPath'] ?? [];
                if (! is_array($dropAreaPath)) {
                    $decoded      = json_decode($dropAreaPath, true);
                    $dropAreaPath = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }

                // die;

                // Check if element already exists
                $existingNode = $xpath->query("//*[@builder-identity='$identity']")->item(0);

                if ($existingNode) {
                    // 🟢 Update existing element
                    if ($tag === 'img') {
                        // handle image src
                        $builder_element_src = explode('/public/', $src);
                        if (array_key_exists(1, $builder_element_src)) {
                            $updated_url_from_builder = "{{ asset('" . $builder_element_src[1] . "') }}";
                        } else {
                            $updated_url_from_builder = "{{ asset('" . $src . "') }}";
                        }
                        $existingNode->setAttribute('src', $updated_url_from_builder);
                    } else {
                        // Replace content safely (keep Blade)
                        while ($existingNode->firstChild) {
                            $existingNode->removeChild($existingNode->firstChild);
                        }

                        $text     = "{{ get_phrase('" . addslashes($content) . "') }}";
                        $textNode = $dom->createTextNode($text);
                        $existingNode->appendChild($textNode);
                    }
                } else {
                    // Create new element
                    if ($tag === 'img') {
                        // handle image src
                        $builder_element_src = explode('/public/', $src);
                        if (array_key_exists(1, $builder_element_src)) {
                            $updated_url_from_builder = "{{ asset('" . $builder_element_src[1] . "') }}";
                        } else {
                            $updated_url_from_builder = "{{ asset('" . $src . "') }}";
                        }
                        $newElement = $dom->createElement($tag, $updated_url_from_builder);
                    } else {
                        // dd($tag);
                        $newElement = $dom->createElement($tag, $content);
                    }
                    // Create new element ended

                    // Extract all attributes from $element and add to $newElement
                    $tempDom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $tempDom->loadHTML(mb_convert_encoding($element, 'HTML-ENTITIES', 'UTF-8'));
                    libxml_clear_errors();
                    $tempXpath = new DOMXPath($tempDom);

                    $tempNode = $tempXpath->query('/')->item(0) ?? $tempXpath->query('/html/body/')->item(0);

                    if ($tempNode !== null) {
                        foreach ($tempNode->attributes ?? [] as $attr) {
                            $newElement->setAttribute($attr->nodeName, $attr->nodeValue);
                        }
                    }
                    // Extract all attributes from $element and add to $newElement ended

                    // Insert the new element at the specific position
                    if ($container) {
                        $dropAreaNode = 0;
                        $dropAreas    = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' drop-area ')]", $container);

                        if ($dropAreas->length > $dropAreaIndex) {
                            $dropAreaNode = $dropAreas->item($dropAreaIndex);
                        }

                        if ($dropAreaNode) {
                            // Insert at specific position
                            $children = [];
                            foreach ($dropAreaNode->childNodes as $child) {
                                if ($child->nodeType === XML_ELEMENT_NODE) {
                                    $children[] = $child;
                                }
                            }
                            $newElement = $dom->importNode($newElement, true);

                            // if the index not exists the insert to the end
                            if (! isset($children[$droppedIndex])) {
                                $dropAreaNode->appendChild($newElement);
                            } else {
                                $dropAreaNode->insertBefore($newElement, $children[$droppedIndex]);
                            }
                        }
                    }

                    // Insert the new element at the specific position ended
                }
            }

            // ✅ Extract only real HTML (skip <html><body>)
            $container = $dom->getElementById('__temp_wrapper__');

            // ✅ Extract only real HTML (skip <html><body>)
            $newHtml = '';
            foreach ($container->childNodes as $child) {
                // Only nodes that belong to $dom
                $newHtml .= $dom->saveHTML($child);
            }

            // ✅ Decode Blade syntax back after DOM processing
            $newHtml = preg_replace_callback('/__BLADE_OPEN__(.*?)__BLADE_CLOSE__/s', function ($matches) {
                return '{{' . base64_decode($matches[1]) . '}}';
            }, $newHtml);

            // ✅ Decode Blade-safe content (restore {{ }}, ->, etc.)
            $newHtml = html_entity_decode($newHtml, ENT_QUOTES | ENT_HTML5);
            $newHtml = urldecode($newHtml);

            // ✅ Save cleaned section (no <html><body> wrapping)
            file_put_contents(
                base_path("resources/views/components/home_made_by_builder/" . $file_name . '.blade.php'),
                $newHtml
            );
        }

        // Update database with built section list
        Builder_page::where('id', $id)->update(['html' => json_encode($built_file_names)]);

        return redirect(route('admin.pages'))->with('success', get_phrase('Page layout has been updated'));
    }

    public function cssToXpath($selector)
    {
        $parts = explode(' > ', $selector);
        $xpath = '';
        foreach ($parts as $part) {
            if (preg_match('/(\w+):nth-of-type\((\d+)\)/', $part, $matches)) {
                $tag = $matches[1];
                $pos = $matches[2];
                $xpath .= '/' . $tag . '[' . $pos . ']';
            } else {
                $xpath .= '/' . $part;
            }
        }
        return $xpath;
    }

    // function builder_page_layout_update(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         // 'developer_elements' => 'required',
    //         'builder_elements' => 'required',
    //     ]);

    //     $get_developer_elements = $this->developer_file_elements();
    //     $post_builder_elements = $request->builder_elements;

    //     $built_file_names = [];
    //     foreach ($post_builder_elements as $file_name => $builder_elements) {
    //         $arr_values = array_values($builder_elements);
    //         if ($arr_values[0]['tag'] == 'null') continue;

    //         $built_file_names[] = $file_name;
    //         $developer_file_content = file_get_contents(base_path('resources/views/components/home_made_by_developer/' . $file_name . '.blade.php'));
    //         $developer_file_content = $this->replace_special_character($developer_file_content);
    //         foreach ($builder_elements as $key => $builder_element) {
    //             $developer_element = $get_developer_elements[$file_name][$key];

    //             if ($this->decodeContent($builder_element['tag']) == 'img') {
    //                 $builder_element_src = explode('/public/', $this->decodeContent($builder_element['src']));

    //                 if (array_key_exists(1, $builder_element_src)) {
    //                     $updated_url_from_builder = "{{asset('" . $builder_element_src[1] . "')}}";
    //                 } else {
    //                     $updated_url_from_builder = "{{asset('" . $this->decodeContent($builder_element['src']) . "')}}";
    //                 }

    //                 $prepared_single_element = str_replace($this->decodeContent($developer_element['src']), $updated_url_from_builder, $this->decodeContent($developer_element['element']));
    //                 $developer_file_content = str_replace($this->decodeContent($developer_element['element']), $prepared_single_element, $developer_file_content);
    //             } elseif ($this->decodeContent($builder_element['tag']) != 'null') {
    //                 $developer_element_tag = $this->replace_special_character($this->decodeContent($developer_element['element']));
    //                 $developer_element_content = $this->replace_special_character($this->decodeContent($developer_element['content']));

    //                 $prepared_single_element = str_replace($developer_element_content, '{{ get_phrase("' . $this->decodeContent($builder_element['content']) . '") }}', $developer_element_tag);
    //                 $prepared_single_element = str_replace('(""', '("', $prepared_single_element);
    //                 $prepared_single_element = str_replace('"")', '")', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase(") }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase() }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{ get_phrase("") }}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase(")}}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase()}}', '', $prepared_single_element);
    //                 $prepared_single_element = str_replace('{{get_phrase("")}}', '', $prepared_single_element);
    //                 $developer_file_content = str_replace($developer_element_tag, $prepared_single_element, $developer_file_content);
    //             }
    //         }

    //         $developer_file_content = str_replace('{{ get_phrase(") }}', '', $developer_file_content);
    //         $developer_file_content = str_replace('{{get_phrase(")}}', '', $developer_file_content);

    //         file_put_contents(base_path("resources/views/components/home_made_by_builder/" . $file_name . '.blade.php'), $developer_file_content);
    //     }
    //     Builder_page::where('id', $id)->update(['html' => json_encode($built_file_names)]);
    //     return redirect(route('admin.pages'))->with('success', get_phrase('Page layout has been updated'));
    // }

    public function decodeContent($content = "")
    {
        if (! $content || empty($content) || $content == 'null') {
            return "null";
        }

        return urldecode(htmlspecialchars_decode(base64_decode($content)));
    }

    public function replace_special_character($text)
    {
        if ($text) {
            $text = str_replace('&amp;', '&', $text);
        }
        return $text;
    }

    public function replace_builder_content($html_1 = "", $html_2 = "")
    {
        //REPLACE $html_1 BY $html_2

        // Extract src and builder-identity attributes from html_2
        preg_match_all('/<img\s+class="builder-editable"\s+builder-identity="(\d+)"\s+src="([^"]+)"/', $html_2, $matches2, PREG_SET_ORDER);

        // Create an associative array to map builder-identity to src
        $srcMap = [];
        foreach ($matches2 as $match) {
            $srcMap[$match[1]] = $match[2];
        }

        // Replace src attributes in html_1 using the srcMap
        $html_1 = preg_replace_callback('/<img\s+class="builder-editable"\s+builder-identity="(\d+)"\s+src="([^"]+)"/', function ($matches) use ($srcMap) {
            $identity = $matches[1];
            if (isset($srcMap[$identity])) {
                return '<img class="builder-editable" builder-identity="' . $identity . '" src="{{asset("' . $srcMap[$identity] . '")}}"';
            }
            return $matches[0];
        }, $html_1);

        // Extract content and builder-identity attributes from html_2 (excluding img tags)
        preg_match_all('/<([^img][^>]*)builder-identity="(\d+)"[^>]*>(.*?)<\/[^>]+>/', $html_2, $matches2, PREG_SET_ORDER);

        // Create an associative array to map builder-identity to content
        $contentMap = [];
        foreach ($matches2 as $match) {
            $contentMap[$match[2]] = $match[3];
        }

        // Replace content in html_1 using the contentMap
        $html_1 = preg_replace_callback('/<([^img][^>]*)builder-identity="(\d+)"[^>]*>(.*?)<\/[^>]+>/', function ($matches) use ($contentMap) {
            $identity = $matches[2];
            if (isset($contentMap[$identity])) {
                return '<' . $matches[1] . 'builder-identity="' . $identity . '">' . $contentMap[$identity] . '<' . substr(strrchr($matches[0], '<'), 1);
            }
            return $matches[0];
        }, $html_1);

        return $html_1;
    }

    public function find_builder_block_elements($html)
    {
        // Define a regex pattern to match all divs with builder-block-file-name attribute
        $pattern = '/<div\s+[^>]*builder-block-file-name="([^"]+)"[^>]*>(.*?)<\/div>/s';

        // Use preg_match_all to find all matches
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        // Collect the file name and HTML content inside each matched element
        $elements = [];
        foreach ($matches as $match) {
            $elements[] = [
                'file_name' => $match[1], // The value of the builder-block-file-name attribute
                'content'   => $match[2], // The inner HTML content of the div
            ];
        }

        return $elements;
    }

    public function developer_file_content()
    {
        //return developer file content
        $developer_file_content = '';
        $files                  = array_diff(scandir(base_path('resources/views/components/home_made_by_developer')), ['.', '..']);
        foreach ($files as $file) {
            $file_name = str_replace('.blade.php', '', $file);
            $developer_file_content .= '<div builder-block-file-name="' . $file_name . '">' . file_get_contents(base_path('resources/views/components/home_made_by_developer/' . $file)) . '</div>';
        }
        return $developer_file_content;
    }

    public function developer_file_elements()
    {
        $developer_file_elements = [];

        $componentPath = base_path('resources/views/components/home_made_by_developer');
        $files         = array_diff(scandir($componentPath), ['.', '..']);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }
            // only .blade.php files

            $fileName = str_replace('.blade', '', pathinfo($file, PATHINFO_FILENAME));
            $filePath = $componentPath . '/' . $file;

            $html = file_get_contents($filePath);

            // Load HTML into DOM
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//*[@builder-identity]');

            $elements = [];

            foreach ($nodes as $node) {
                $identity = $node->getAttribute('builder-identity');
                $tag      = $node->nodeName;

                // Get full element HTML
                $elementHTML = $dom->saveHTML($node);

                // Detect src (for images)
                $src = $node->hasAttribute('src') ? $node->getAttribute('src') : null;

                // Get inner content (without wrapping tag)
                $content = '';
                foreach ($node->childNodes as $child) {
                    $content .= $dom->saveHTML($child);
                }

                // 🔹 Get selector path
                $selector = $this->getDomSelectorPath($node);

                $elements[$identity] = [
                    'element'  => $elementHTML,
                    'tag'      => $tag,
                    'identity' => $identity,
                    'selector' => $selector,
                    'content'  => $content ? $content : null,
                    'src'      => $src ? $src : null,
                ];
            }

            $developer_file_elements[$fileName] = $elements;
        }

        return $developer_file_elements;
    }

    private function queryDomBySelector($xpath, $selector)
    {
        // Convert simple selectors like "html > body > div:nth-of-type(2) > p"
        $selector = preg_replace('/:(nth-of-type\((\d+)\))/', '[$2]', $selector);
        $selector = str_replace(' > ', '/', $selector);

        $selector = '//' . preg_replace('/html/', '', $selector);
        $nodes    = @$xpath->query($selector);
        return $nodes->item(0) ?? null;
    }

    public function getDomSelectorPath(DOMNode $node)
    {
        $path = [];

        while ($node && $node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'html') {
            $tag = strtolower($node->nodeName);

            // Find position among siblings of same tag
            $index   = 1;
            $sibling = $node->previousSibling;
            while ($sibling) {
                if ($sibling->nodeType === XML_ELEMENT_NODE && $sibling->nodeName === $node->nodeName) {
                    $index++;
                }
                $sibling = $sibling->previousSibling;
            }

            // Add nth-of-type if needed
            $selector = $tag . ($index > 1 ? ":nth-of-type($index)" : '');
            array_unshift($path, $selector);

            $node = $node->parentNode;
        }

        return 'html > ' . implode(' > ', $path);
    }

    public function page_layout_image_update(Request $request)
    {
        $remove_file_arr     = explode('/', $request->remove_file);
        $previous_image_path = 'uploads/home-page-builder/' . end($remove_file_arr);
        remove_file($previous_image_path);

        $image_path = FileUploader::upload($request->file, 'uploads/home-page-builder');
        return get_image($image_path);
    }

    public function preview($page_id)
    {
        $page_data['page_id'] = $page_id;
        return view('frontend.default.home.preview', $page_data);
    }
}
