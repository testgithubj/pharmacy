import http from 'k6/http';
import { sleep } from 'k6';

export default function () {
  http.get('http://13.233.97.7/');
  sleep(1);
}
