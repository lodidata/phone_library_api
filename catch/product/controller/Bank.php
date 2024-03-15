<?php
namespace catchAdmin\product\controller;

use catcher\base\CatchController;
use catcher\base\CatchRequest;
use catcher\CatchResponse;
use catchAdmin\product\validate\BankValidate;
use catcher\exceptions\ValidateFailedException;
use catchAdmin\product\model\Bank as BankModel;
use catchAdmin\product\model\MemberCharge as MemberChargeModel;

class Bank extends CatchController
{
  protected $bank;

  public function __construct(BankModel $bank)
  {
    $this->bank = $bank;
  }

  /**
   * 列表
   *
   * @param CatchRequest $request
   * @return \think\response\Json
   * @throws \think\db\exception\DbException
   */
  public function index()
  {
      $chargeModel = new MemberChargeModel();

      $list = $this->bank->getList();
      foreach ($list as $item => &$value){
        $value['accounts'] = $chargeModel->where(['bank_id' => $value['id'], 'status' =>1])->sum('amount');
      }
      unset($value);
    return CatchResponse::paginate($list);
  }

    /**
     * 读取
     *
     * @param CatchRequest $request
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function read($id)
    {
        $field = [
            'id',
            'bank_account',
            'bank_name',
            'bank_code',
            'bank_address',
            'sort',
            'status'
        ];
        return CatchResponse::success($this->bank->findby($id,$field));
    }

  /**
   * 保存
   *
   * @param catchRequest $request
   * @return \think\response\Json
   */
  public function save(catchRequest $request)
  {
      try{
          $params = $request->param();
          $va = new BankValidate();
          $result = $va->check($params);
          if(!$result){
              throw new ValidateFailedException($va->getError());
          }
          $params['status'] = $this->bank::ENABLE;
          $this->bank->storeBy($params);
      } catch (\Exception $e) {
          return CatchResponse::fail($e->getMessage());
      }
    return CatchResponse::success('保存成功');
  }

  /**
   * 更新
   *
   * @time 2020年01月09日
   * @param $id
   * @param CatchRequest $request
   * @return \think\response\Json
   */
  public function update($id, CatchRequest $request)
  {
      try{
          $bankInfo = $this->bank->findby($id);
          if(!$bankInfo){
              throw new \Exception('编辑银行卡信息不存在');
          }
          $params = $request->param();
          $va = new BankValidate();
          $result = $va->check($params);
          if(!$result){
              throw new ValidateFailedException($va->getError());
          }
          $params['status'] = $params['status'] == $this->bank::ENABLE ?: $this->bank::DISABLE;
          $this->bank->updateBy($id, $params);
      } catch (\Exception $e) {
          return CatchResponse::fail($e->getMessage());
      }
    return CatchResponse::success('更新成功');
  }

    /**
     * 银行卡 修改状态
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editStatus(CatchRequest $request, $id)
    {
        $params = $request->param();
        $status = (int)$params['status'];

        $this->bank->updateBy((int)$id, ['status' => $status]);

        return CatchResponse::success([], '操作成功');
    }

  /**
   * 删除
   *
   * @time 2020年01月09日
   * @param $id
   * @return \think\response\Json
   */
  public function delete($id)
  {
    return CatchResponse::success($this->bank->deleteBy($id));
  }
}
