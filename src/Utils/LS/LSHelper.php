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
            case "BOTTOM_DUO_CARRY":
            case "BOTTOM_NONE":
            case "BOTTOM_SOLO":
            case "BOT_CARRY":
            case "BOT_SOLO":
            case "BOT_DUO":
            case "NONE_DUO":
            case "BOTTOM_DUO":
                $role = "Bot";
                break;
            case "BOT_SUPPORT":
            case "N/A_DUO":
            case "N/A_SUPPORT":
            case "NONE_DUO_SUPPORT":
            case "BOTTOM_DUO_SUPPORT":
                $role = "Support";
                break;
            case "JUNGLE_N/A":
            case "JUNGLE_NONE":
                $role = "Jungle";
                break;
            case "MIDDLE_DUO_CARRY":
            case "MIDDLE_DUO_SUPPORT":
            case "MIDDLE_NONE":
            case "MIDDLE_SOLO":
            case "MIDDLE_SUPPORT":
            case "MIDDLE_CARRY":
            case "MIDDLE_DUO":
                $role = "Mid";
                break;
            case "TOP_DUO_CARRY":
            case "TOP_DUO_SUPPORT":
            case "TOP_SOLO":
            case "TOP_NONE":
            case "TOP_SUPPORT":
            case "TOP_CARRY":
            case "TOP_DUO":
                $role = "Top";
                break;
            case "N/A_N/A":
            case "NONE_NONE":
                $role = "Unknown";
                break;
            default:
                $role = $cRole;
                break;
        }

        return $role;
    }

}