<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 04.12.2018
 * Time: 19:48
 */

namespace App\Utils\LS;


class LSHelper
{

    /**
     * @param string $cRole
     * @return string
     */
    public static function get_role(string $cRole)
    {
        switch ($cRole) {
            case "BOTTOM_DUO":
                $role = "Bot";
                break;
            case "BOTTOM_DUO_CARRY":
                $role = "Bot";
                break;
            case "BOTTOM_DUO_SUPPORT":
                $role = "Support";
                break;
            case "BOTTOM_NONE":
                $role = "Bot";
                break;
            case "BOTTOM_SOLO":
                $role = "Bot";
                break;
            case "JUNGLE_NONE":
                $role = "Jungle";
                break;
            case "MIDDLE_DUO":
                $role = "Mid";
                break;
            case "MIDDLE_DUO_CARRY":
                $role = "Mid";
                break;
            case "MIDDLE_DUO_SUPPORT":
                $role = "Mid";
                break;
            case "MIDDLE_NONE":
                $role = "Mid";
                break;
            case "MIDDLE_SOLO":
                $role = "Mid";
                break;
            case "NONE_NONE":
                $role = "Unknown";
                break;
            case "TOP_DUO":
                $role = "Top";
                break;
            case "TOP_DUO_CARRY":
                $role = "Top";
                break;
            case "TOP_DUO_SUPPORT":
                $role = "Top";
                break;
            case "TOP_SOLO":
                $role = "Top";
                break;
            case "TOP_NONE":
                $role = "Top";
                break;
            case "BOT_CARRY":
                $role = "Bot";
                break;
            case "BOT_SUPPORT":
                $role = "Support";
                break;
            case "BOT_SOLO":
                $role = "Bot";
                break;
            case "BOT_DUO":
                $role = "Bot";
                break;
            case "TOP_SUPPORT":
                $role = "Top";
                break;
            case "MIDDLE_SUPPORT":
                $role = "Mid";
                break;
            case "MIDDLE_CARRY":
                $role = "Mid";
                break;
            case "N/A_DUO":
                $role = "Support";
                break;
            case "N/A_SUPPORT":
                $role = "Support";
                break;
            case "JUNGLE_N/A":
                $role = "Jungle";
                break;
            case "N/A_N/A":
                $role = "Unknown";
                break;
            case "TOP_CARRY":
                $role = "Top";
                break;
            case "NONE_DUO":
                $role = "Bot";
                break;
            case "NONE_DUO_SUPPORT":
                $role = "Support";
                break;
            default:
                $role = $cRole;
                break;
        }

        return $role;
    }

}