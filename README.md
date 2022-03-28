# opencloud-video-player
|PC|모바일|
|-|-|
|![image](https://user-images.githubusercontent.com/24975076/160377148-603cb0eb-6d75-4419-85e4-af6782dd62d3.png)|![image](https://user-images.githubusercontent.com/24975076/160377290-a6e2cc81-476c-4cfe-b62e-3ba5a1a7de9a.png)|

video.js, php Based Web Video Player (for Opencloud)  
오픈클라우드 전용 웹 비디오 플레이어
### [데모 링크](https://demo.pbj.kr/opencloud-video-player)

## 사용방법
```html
<a href='https://myserver.com/videoplayer?video=/your/video.mp4' target='_blank'>video.mp4</a>
```

## 특징
- 이전, 다음 비디오 바로 탐색 가능
- 조회수 확인 (DB 연동시)
- 짧은 공유 링크 생성 및 복사 (DB 연동시)
- 기본적인 다운로드 방지 기능 (우클릭 방지)
- vtt(srt) 자막 지원

## 사용된 프로젝트
- Bootstrap 5
  - https://getbootstrap.com/docs/5.0/
- video.js
  - https://videojs.com
- PHP HTML5 Video Streaming Tutorial
  - https://codesamplez.com/programming/php-html5-video-streaming-tutorial

## 참고사항
- php를 통한 비디오 스트리밍이 가능하나(주석 & streaming.php 참고), 라즈베리 파이와 같은 저사양 환경에서는 틈만 나면 뻗을 수 있으므로 비디오 스트리밍 대역폭을 제한하고 싶은 경우 서버 설정을 통해 제한하는것을 추천합니다
