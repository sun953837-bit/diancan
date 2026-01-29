from fastapi import FastAPI
from pydantic import BaseModel

app = FastAPI(title="Worker Service")


class TaskPayload(BaseModel):
    task_type: str
    payload: dict


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/tasks/dispatch")
def dispatch_task(payload: TaskPayload):
    """
    异步任务入口（占位）
    - 物流轨迹同步
    - BI 导出
    - 支付补偿
    """
    return {"code": 0, "msg": "queued", "data": payload.model_dump()}
