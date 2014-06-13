<?php
/**
 * Created by PhpStorm.
 * User: Sergei
 * Date: 16.02.14
 * Time: 13:49
 */

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;

class AVPFactory
{

    public static function createAVP($params)
    {
        if (isset($params['avp_raw_data'])) {
            return self::import($params['avp_raw_data']);
        }
        if (is_numeric($params) && $params >= 0) { // Create Raw AVP:
            return self::createNew($params);
        }
        throw new \Exception("Unknown parameters for " . __METHOD__ . ".");
    }

    protected static function import($avpRawData)
    {
        list(, $avpType) = unpack('n', $avpRawData[4] . $avpRawData[5]);
        switch ($avpType) {
            case AvpType::MESSAGE_TYPE_AVP:
                $avp = MessageTypeAVP::import($avpRawData);
                break;
            case AvpType::PROTOCOL_VERSION_AVP:
                $avp = ProtocolVersionAVP::import($avpRawData);
                break;
            case AvpType::HOSTNAME_AVP:
                $avp = HostnameAVP::import($avpRawData);
                break;
            case AvpType::FRAMING_CAPABILITIES_AVP:
                $avp = FramingCapabilitiesAVP::import($avpRawData);
                break;
            case AvpType::BEARER_CAPABILITIES_AVP:
                $avp = BearerCapabilitiesAVP::import($avpRawData);
                break;
            case AvpType::FIRMWARE_REVISION_AVP:
                $avp = FirmwareRevisionAVP::import($avpRawData);
                break;
            case AvpType::ASSIGNED_TUNNEL_ID_AVP:
                $avp = AssignedTunnelIdAVP::import($avpRawData);
                break;
            case AvpType::RECEIVE_WINDOW_SIZE_AVP:
                $avp = ReceiveWindowSizeAVP::import($avpRawData);
                break;
            case AvpType::VENDOR_NAME_AVP:
                $avp = VendorNameAVP::import($avpRawData);
                break;
            default:
                // default AVPs
                var_dump($avpType);
                $avp = UnrecognizedAVP::import($avpRawData);
        }
        return $avp;
    }

    protected static function createNew($avpType)
    {
        switch ($avpType) {
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
            case AvpType::VENDOR_NAME_AVP:
                $avp = new VendorNameAVP();
                break;
            default:
                // default AVPs
                throw new \Exception("Trying to create unknown AVP");
                $avp = new UnrecognizedAVP();
        }
        return $avp;
    }

} 