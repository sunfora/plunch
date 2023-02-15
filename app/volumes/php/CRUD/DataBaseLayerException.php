<?
namespace Plunch\CRUD;

class DataBaseLayerException extends \RuntimeException {}
class NoSuchDataException extends DataBaseLayerException {}
class AlreadyExistsException extends DataBaseLayerException {}
