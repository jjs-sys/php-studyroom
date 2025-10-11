# 📚 스터디룸 예약 시스템 (CodeIgniter4)

스터디룸 예약 및 관리 시스템입니다.  
사용자는 웹에서 예약 및 인증을 진행할 수 있고, 관리자는 별도의 페이지에서 요금 및 회원정보를 수정하거나 예약을 삭제할 수 있습니다.

---

## 구현 화면
예약화면
![예약 화면](public/image/main01.png)

휴대폰 인증
![예약 화면](public/image/main02.png)
![예약 화면](public/image/main03.png)

예약 완료
![예약 화면](public/image/main04.png)

예약 조회(과겨내역 미포함)
![예약 화면](public/image/main05.png)
예약 조회(과거내역 포함)
![예약 화면](public/image/main06.png)

관리자 페이지
![예약 화면](public/image/main07.png)

restapi를 이용한 수정
![예약 화면](public/image/main08.png)

수정된 데이터
![예약 화면](public/image/main09.png)





## 🧱 기술 스택

- **언어**: PHP 7.4
- **프레임워크**: CodeIgniter 4.x
- **DB**: MySQL 8 / MariaDB 10.6+
- **패키지 매니저**: Composer 2.x
- **타임존**: Asia/Seoul
- **로컬 서버**: `php spark serve`

---

## ⚙️ 설치 및 실행

### 1️⃣ 프로젝트 클론 및 의존성 설치
```bash
git clone https://github.com/yourname/studyroom-ci4.git
cd studyroom-ci4
composer install
2️⃣ .env 설정
.env 파일이 없으면 루트에 새로 생성하고 아래 내용을 참고하세요 👇

bash
코드 복사
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080'
app.appTimezone = Asia/Seoul
app.defaultLocale = ko

database.default.hostname = 127.0.0.1
database.default.database = studyroom
database.default.username = root
database.default.password = 12345
database.default.DBDriver = MySQLi
database.default.port = 3306
💡 .env 파일에서 #은 주석입니다. 꼭 제거하세요.

🗄️ DB 초기화 및 시드
1️⃣ 마이그레이션 실행
bash
코드 복사
php spark migrate
2️⃣ 기본 데이터(지점/룸) 자동 생성
마이그레이션(CreateStudyroomTables) 실행 시 기본 데이터가 자동 추가됩니다:

지점(branches)

강남점

홍대점

룸(rooms)

A룸 (강남/홍대 공통)

B룸 (강남만)

🚀 로컬 서버 실행
bash
코드 복사
php spark serve
실행 후 브라우저에서 아래 주소로 접근하세요 👇

구분	URL
예약 페이지	http://localhost:8080/view/reserve
예약 조회	http://localhost:8080/view/find
관리자 페이지	http://localhost:8080/view/admin

🔧 관리자 기능 (REST API)
기능	URL 예시
가격 수정	/admin/update-price/{예약ID}/{새가격}
회원 정보 수정	/admin/update-member/{회원ID}/{이름}/{전화번호}
예약 삭제	/admin/delete/{예약ID}

예시 👇

bash
코드 복사
http://localhost:8080/admin/update-price/3/9000
http://localhost:8080/admin/update-member/2/홍길동/01099998888
http://localhost:8080/admin/delete/5
🧪 테스트 계정 / 데이터
항목	예시
Branch	강남점 (ID: 1), 홍대점 (ID: 2)
Room	A (ID: 1), B (ID: 2)
회원 예시	이름: 김민수, 전화번호: 01012345678