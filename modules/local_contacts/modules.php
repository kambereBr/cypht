<?php

/**
 * Local contact modules
 * @package modules
 * @subpackage local_contacts
 */

if (!defined('DEBUG_MODE')) { die(); }

/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_process_add_contact_from_message extends Hm_Handler_Module {
    public function process() {
        list($success, $form) = $this->process_form(array('contact_source', 'contact_value'));
        if (!$success) {
            return;
        }
        list($type, $source) = explode(':', $form['contact_source']);
        if ($type == 'local' && $source == 'local') {
            $addresses = Hm_Address_Field::parse($form['contact_value']);
            if (!empty($addresses)) {
                $contacts = $this->get('contact_store');
                foreach ($addresses as $vals) {
                    $contacts->add_contact(array('source' => 'local', 'email_address' => $vals['email'], 'display_name' => $vals['name']));
                }
                $this->user_config->set('contacts', $contacts->export());
                $this->session->record_unsaved('Contact Added');
                Hm_Msgs::add('Contact Added');
            }
        }
    }
}

/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_process_delete_contact extends Hm_Handler_Module {
    public function process() {
        $contacts = $this->get('contact_store');
        list($success, $form) = $this->process_form(array('contact_type', 'contact_source', 'contact_id'));
        if ($success && $form['contact_type'] == 'local' && $form['contact_source'] == 'local') {
            if ($contacts->delete($form['contact_id'])) {
                $this->user_config->set('contacts', $contacts->export());
                $this->session->record_unsaved('Contact deleted');
                $this->out('contact_deleted', 1);
                Hm_Msgs::add('Contact Deleted');
            }
        }
    }
}

/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_process_add_contact extends Hm_Handler_Module {
    public function process() {
        $contacts = $this->get('contact_store');
        list($success, $form) = $this->process_form(array('contact_source', 'contact_email', 'contact_name', 'add_contact'));
        if ($success && $form['contact_source'] == 'local') {
            $details = array('source' => 'local', 'email_address' => $form['contact_email'], 'display_name' => $form['contact_name']);
            if (array_key_exists('contact_phone', $this->request->post) && $this->request->post['contact_phone']) {
                $details['phone_number'] = $this->request->post['contact_phone'];
            }
            $contacts->add_contact($details);
            $this->user_config->set('contacts', $contacts->export());
            $this->session->record_unsaved('Contact Added');
            Hm_Msgs::add('Contact Added');
        }
    }
}


/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_process_import_contact extends Hm_Handler_Module {
    public function process() {
        list($success, $form) = $this->process_form(array('contact_source', 'import_contact'));
        if ($success && $form['contact_source'] == 'csv') {
            $file = $this->request->files['contact_csv'];
            $csv = fopen($file['tmp_name'], 'r');
            if ($csv) {
                $contacts = $this->get('contact_store');
                $header = fgetcsv($csv);
                $expectedHeader = array('display_name', 'email_address', 'phone_number');

                if ($header !== $expectedHeader) {
                    fclose($csv);
                    Hm_Msgs::add('ERRInvalid CSV file, please use a valid header: '.implode(', ', $expectedHeader));
                    return;
                }

                $contact_list = $this->user_config->get('contacts', array());
                $contact_list = array_map(function($v) { $v['type'] = 'local'; return $v; }, $contact_list);
                $message = '';
                $update_count = 0;
                $create_count = 0;
                $invalid_mail_count = 0;
                $import_result = [
                    'create' => '',
                    'update' => '',
                    'invalid' => '',
                ];

                while (($data = fgetcsv($csv)) !== FALSE) {
                    $email = $data[1];
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $import_result['invalid'] .= $email.',';
                        $invalid_mail_count++;
                        continue;
                    }

                    $details = array('source' => 'local', 'display_name' => $data[0], 'email_address' => $email);
                    if (array_key_exists(2, $data) && $data[2]) {
                        $details['phone_number'] = $data[2];
                    }

                    $contactUpdated = false;

                    foreach ($contact_list as $key => $contact) {
                        if ($contact['email_address'] == $email) {
                            $contacts->update_contact($key, $details);
                            $import_result['update'] .= $email.',';
                            $update_count++;
                            $contactUpdated = true;
                            continue 2;
                        }
                    }

                    if (!$contactUpdated) {
                        $contacts->add_contact($details);
                        $import_result['create'] .= $email.',';
                        $create_count++;
                    }
                }
                fclose($csv);
                $this->user_config->set('contacts', $contacts->export());
                $this->session->record_unsaved('Contact Created');
                if (isset($import_result) && (!$create_count && !$update_count)) {
                    $message = 'ERR'.$create_count.' contacts created, '.$update_count.' contacts updated, '.$invalid_mail_count.' Invalid email address';
                } elseif (isset($import_result) && ($create_count || $update_count)) {
                    $message = $create_count.' contacts created, '.$update_count.' contacts updated, '.$invalid_mail_count.' Invalid email address'; 
                } else {
                    $message = 'ERRAn error occured';
                }
                $this->session->set('contact_imported', $import_result);
                // var_dump($this->session->get('contact_imported', array())); die;
                
                Hm_Msgs::add($message);
            }
        }
    }
}


/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_process_edit_contact extends Hm_Handler_Module {
    public function process() {
        $contacts = $this->get('contact_store');
        list($success, $form) = $this->process_form(array('contact_source', 'contact_id', 'contact_email', 'contact_name', 'edit_contact'));
        if ($success && $form['contact_source'] == 'local') {
            $details = array('email_address' => $form['contact_email'], 'display_name' => $form['contact_name']);
            if (array_key_exists('contact_phone', $this->request->post)) {
                $details['phone_number'] = $this->request->post['contact_phone'];
            }
            if ($contacts->update_contact($form['contact_id'], $details)) {
                $this->user_config->set('contacts', $contacts->export());
                $this->session->record_unsaved('Contact updated');
                Hm_Msgs::add('Contact Updated');
            }
        }
    }
}

/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_load_edit_contact extends Hm_Handler_Module {
    public function process() {
        if (array_key_exists('contact_source', $this->request->get) && $this->request->get['contact_source'] == 'local'
            && array_key_exists('contact_type', $this->request->get) && $this->request->get['contact_type'] == 'local' &&
            array_key_exists('contact_id', $this->request->get)) {

            $contacts = $this->get('contact_store');
            $contact = $contacts->get($this->request->get['contact_id']);
            if (is_object($contact)) {
                $current = $contact->export();
                $current['id'] = $this->request->get['contact_id'];
                $this->out('current_contact', $current);
            }
        }
    }
}

/**
 * @subpackage local_contacts/handler
 */
class Hm_Handler_load_local_contacts extends Hm_Handler_Module {
    public function process() {
        // var_dump($this->session->get('contact_imported', array())); die;
        $contacts = $this->get('contact_store');
        $contact_list = $this->user_config->get('contacts', array());
        $contact_list = array_map(function($v) { $v['type'] = 'local'; return $v; }, $contact_list);
        $contacts->import($contact_list);
        $this->append('contact_sources', 'local');
        $this->out('contact_store', $contacts, false);
        $this->append('contact_edit', 'local:local');
    }
}

/**
 * @subpackage local_contacts/output
 */
class Hm_Output_contacts_form extends Hm_Output_Module {
    protected function output() {

        $email = '';
        $name = '';
        $phone = '';
        $form_class = 'contact_form';
        $button = '<input class="add_contact_submit" type="submit" name="add_contact" value="'.$this->trans('Add').'" />';
        $title = $this->trans('Add Local');
        $current = $this->get('current_contact', array());
        if (!empty($current)) {
            if (array_key_exists('email_address', $current)) {
                $email = $current['email_address'];
            }
            if (array_key_exists('display_name', $current)) {
                $name = $current['display_name'];
            }
            if (array_key_exists('phone_number', $current)) {
                $phone = $current['phone_number'];
            }
            $form_class = 'contact_update_form';
            $title = $this->trans('Update Local');
            $button = '<input type="hidden" name="contact_id" value="'.$this->html_safe($current['id']).'" />'.
                '<input class="edit_contact_submit" type="submit" name="edit_contact" value="'.$this->trans('Update').'" />';
        }
        return '<div class="add_contact"><form class="add_contact_form" method="POST">'.
            '<div class="server_title">'.$title.
            '<img alt="" class="menu_caret" src="'.Hm_Image_Sources::$chevron.'" width="8" height="8" /></div>'.
            '<div class="'.$form_class.'">'.
            '<input type="hidden" name="contact_source" value="local" />'.
            '<input type="hidden" name="hm_page_key" value="'.$this->html_safe(Hm_Request_Key::generate()).'" />'.
            '<label class="screen_reader" for="contact_email">'.$this->trans('E-mail Address').'</label>'.
            '<input required placeholder="'.$this->trans('E-mail Address').'" id="contact_email" type="email" name="contact_email" '.
            'value="'.$this->html_safe($email).'" /> *<br />'.
            '<label class="screen_reader" for="contact_name">'.$this->trans('Full Name').'</label>'.
            '<input required placeholder="'.$this->trans('Full Name').'" id="contact_name" type="text" name="contact_name" '.
            'value="'.$this->html_safe($name).'" /> *<br />'.
            '<label class="screen_reader" for="contact_phone">'.$this->trans('Telephone Number').'</label>'.
            '<input placeholder="'.$this->trans('Telephone Number').'" id="contact_phone" type="text" name="contact_phone" '.
            'value="'.$this->html_safe($phone).'" /><br />'.$button.' <input type="button" class="reset_contact" value="'.
            $this->trans('Cancel').'" /></div></form></div>';
    }
}

/**
 * @subpackage import_local_contacts/output
 */
class Hm_Output_import_contacts_form extends Hm_Output_Module {
    protected function output() {
        $form_class = 'contact_form';
        $button = '<input class="add_contact_submit" type="submit" name="import_contact" id="import_contact" value="'.$this->trans('Add').'" />';
        $notice = 'Please ensure your CSV header file follows the format: display_name, email_address, phone_number';
        $title = $this->trans('Import from CSV file');
        $csv_sample_path = WEB_ROOT.'modules/local_contacts/assets/data/contact_sample.csv';
        
        return '<div class="add_contact"><form class="add_contact_form" method="POST" id="import_form" enctype="multipart/form-data">'.
            '<div class="server_title" title="'.$notice.'">'.$title.
            '<img alt="" class="menu_caret" src="'.Hm_Image_Sources::$chevron.'" width="8" height="8" /></div>'.
            '<div class="'.$form_class.'">'.
            '<div><a href="'.$csv_sample_path.'">'.$this->trans('download a sample csv file').'</a></div><br />'.
            '<input type="hidden" name="contact_source" value="csv" />'.
            '<input type="hidden" name="hm_page_key" value="'.$this->html_safe(Hm_Request_Key::generate()).'" />'.
            '<label class="screen_reader" for="contact_csv">'.$this->trans('Csv File').'</label>'.
            '<input required id="contact_csv" type="file" name="contact_csv" accept=".csv"/> *<br />'.
            $button.' <input type="button" class="reset_contact" value="'.
            $this->trans('Cancel').'" /></div></form></div>';
    }
}
