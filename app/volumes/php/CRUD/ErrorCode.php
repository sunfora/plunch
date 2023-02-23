<?
namespace Plunch\CRUD;

enum ErrorCode : int {
    case DUP_ENTRY = 1062;
    case NO_REFERENCED_ROW = 1452;
    case ROW_IS_REFERENCED = 1451;

    public function explain_in_default_way() : string {
        return match($this) {
            ErrorCode::DUP_ENTRY => "already exists",
            ErrorCode::NO_REFERENCED_ROW => "provided data does not exist",
            ErrorCode::ROW_IS_REFERENCED => "value is still in use"
        };
    }
}
