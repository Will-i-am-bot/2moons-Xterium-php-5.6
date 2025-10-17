<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan <info@2moons.cc>
 * @copyright 2006 Perberos <ugamela@perberos.com.ar> (UGamela)
 * @copyright 2008 Chlorel (XNova)
 * @copyright 2009 Lucky (XGProyecto)
 * @copyright 2012 Jan <info@2moons.cc> (2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0 (2012-11-31)
 * @info $Id: Mail.class.php 2662 2013-04-01 20:40:08Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

class Mail
{
        static public function send($mailTarget, $mailTargetName, $mailSubject, $mailContent)
        {
                $mail   = self::getMailObject();

                $mailFromAdress = self::getSenderAddress();
                $mailFromName   = Config::get('game_name');

        $mail->CharSet          = 'UTF-8';
        $mail->Subject          = $mailSubject;
        $mail->Body             = $mailContent;
        $mail->SetFrom($mailFromAdress, $mailFromName); // FIXED: added valid sender address for PHPMailer
        self::addValidatedAddress($mail, $mailTarget, $mailTargetName);
        $mail->Send();
        }

        static public function multiSend($mailTargets, $mailSubject, $mailContent = NULL)
        {
                $mail   = self::getMailObject();

                $mailFromAdress = self::getSenderAddress();
                $mailFromName   = Config::get('game_name');

        $mail->CharSet          = 'UTF-8';
        $mail->SetFrom($mailFromAdress, $mailFromName); // FIXED: added valid sender address for PHPMailer
        $mail->Subject          = $mailSubject;

                foreach($mailTargets as $address => $data)
                {
                        $content = isset($data['body']) ? $data['body'] : $mailContent;

                        self::addValidatedAddress($mail, $address, $data['username']);
                        $mail->MsgHTML($content);
                        $mail->Send();
                        $mail->ClearAddresses();
                }
        }

        static private function addValidatedAddress(PHPMailer $mail, $address, $name = '')
        {
                if(!filter_var($address, FILTER_VALIDATE_EMAIL))
                {
                        throw new Exception('Invalid recipient address'); // FIXED: ensure only valid recipient emails are used
                }

                $mail->AddAddress($address, $name);
        }

        static private function getSenderAddress()
        {
                $mailFromAdress = Config::get('smtp_sendmail');
                $smtpUser       = Config::get('smtp_user');

                if(!filter_var($mailFromAdress, FILTER_VALIDATE_EMAIL))
                {
                        if(filter_var($smtpUser, FILTER_VALIDATE_EMAIL))
                        {
                                $mailFromAdress = $smtpUser; // FIXED: fallback to configured SMTP user
                        }
                        else
                        {
                                $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain');
                                $host = preg_replace('/[^a-z0-9\.-]/i', '', $host);

                                if(strpos($host, '.') === false)
                                {
                                        $host = 'localhost.localdomain';
                                }

                                $mailFromAdress = 'no-reply@'.$host; // FIXED: fallback to domain-based address
                        }
                }

                return $mailFromAdress;
        }

        static private function getMailObject()
        {
        require 'includes/libs/phpmailer/class.phpmailer.php';
        $mail                   = new PHPMailer(true);
                $mail->PluginDir                = 'includes/libs/phpmailer/';

        if(Config::get('mail_use') == 2) {
                        $mail->IsSMTP();
                        $mail->SMTPSecure       = Config::get('smtp_ssl');
                        $mail->Host             = Config::get('smtp_host');
                        $mail->Port             = Config::get('smtp_port');

                        if(Config::get('smtp_user') != '')
                        {
                                $mail->SMTPAuth         = true;
                                $mail->Username         = Config::get('smtp_user');
                                $mail->Password         = Config::get('smtp_pass');
                        }
        } elseif(Config::get('mail_use') == 0) {
                        $mail->IsMail();
        } else {
                        throw new Exception("Sendmail is deprecated, use SMTP instaed!");
                }

                return $mail;
        }
}
