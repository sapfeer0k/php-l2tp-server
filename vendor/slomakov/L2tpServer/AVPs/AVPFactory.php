<?php
/**
 * Created by PhpStorm.
 * User: Sergei
 * Date: 16.02.14
 * Time: 13:49
 */

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;

class AVPFactory {
    static function createAVP($params) {
        $avpRawData = NULL;
        $avpType = NULL;
        // --
        if (isset($params['avp_raw_data'])) {
            $avpRawData = $params['avp_raw_data'];
            list( , $first_byte) = unpack('C', $avpRawData[0]);
            if ( $first_byte & 60 ) { // check for reserved bits
                $avpType = 'unrecognised';
            }
            list( , $avpType) = unpack('n', $avpRawData[4].$avpRawData[5]);
        } elseif (is_numeric($params) && $params >= 0) {
            $avpType = $params;
        } else {
            throw new \Exception("Unknown parameters for ".__METHOD__.".");
        }
        switch($avpType) {
            case AvpType::MESSAGE_TYPE_AVP:
                $avp = new MessageTypeAVP();
                break;
            case AvpType::PROTOCOL_VERSION_AVP:
                $avp = new ProtocolVersionAVP();
                break;
            case AvpType::HOSTNAME_AVP:
                $avp = new HostnameAVP();
                break;
            case AvpType::FRAMING_CAPABILITIES_AVP:
                $avp = new FramingCapabilitiesAVP();
                break;
            case AvpType::BEARER_CAPABILITIES_AVP:
                $avp = new BearerCapabilitiesAVP();
                break;
            case AvpType::FIRMWARE_REVISION_AVP:
                $avp = new FirmwareRevisionAVP();
                break;
            case AvpType::ASSIGNED_TUNNEL_ID_AVP:
                $avp = new AssignedTunnelIdAVP();
                break;
            case AvpType::RECEIVE_WINDOW_SIZE_AVP:
                $avp = new ReceiveWindowSizeAVP();
                break;
            default:
                // default AVPs
                $avp = new UnrecognizedAVP($avpRawData);
        }
        if ($avpRawData) {
            $avp->import($avpRawData);
        }
        return $avp;
    }

} 