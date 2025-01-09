/* 優化後的完整程式碼 */

// 設置 header 在下滑時背景圖片縮小
window.addEventListener('scroll', function () {
    const header = document.querySelector('header');
    if (window.scrollY > 0) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// 設置網頁下滑時 back_to_top 按鈕出現
window.addEventListener('scroll', function () {
    const back_to_top = document.getElementById('back_to_top');
    if (window.scrollY > 0) {
        back_to_top.classList.add('scroll');
    } else {
        back_to_top.classList.remove('scroll');
    }
});

// RWD 改變主頁 title 縮排變成選單
document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    const navigation = document.getElementById('navigation');
    const text1 = document.getElementById('text1');

    // 切換選單的顯示狀態
    menuToggle.addEventListener('click', function () {
        menuToggle.classList.toggle('active');
        navigation.classList.toggle('active');
    });

    // 根據視窗寬度更新 #text1 內容
    function updateTextContent() {
        if (window.matchMedia('(max-width: 1100px)').matches) {
            text1.textContent = '用心嚴選高品質水果禮盒的水果行';
        } else {
            text1.textContent = '--------------------用心嚴選高品質水果禮盒的水果行--------------------';
        }
    }

    // 初始化檢查與監聽視窗大小改變
    updateTextContent();
    window.addEventListener('resize', updateTextContent);
});

// 生成無重複的 10 位亂碼
function generateUniqueRandomNumbers() {
    const digits = Array.from({ length: 10 }, (_, i) => i);
    for (let i = digits.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [digits[i], digits[j]] = [digits[j], digits[i]];
    }
    return digits.join('');
}

// 檢查 localStorage 中是否有 user_id，若無則生成新亂碼並儲存到伺服器
function initializeUserSession() {
    let user_id = localStorage.getItem('user_id');

    if (!user_id) {
        user_id = generateUniqueRandomNumbers();
        localStorage.setItem('user_id', user_id);
        console.log('使用者 UserId (新亂碼):', user_id);
    } else {
        console.log('使用者已存在 user_id:', user_id);
    }

    // 將 user_id 傳送到後端儲存於 Session
    fetch('store_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: user_id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('伺服器 Session 設置成功:', data.message);
            } else {
                console.error('伺服器 Session 設置失敗:', data.message);
            }
        })
        .catch(error => {
            console.error('Session 設置 AJAX 發生錯誤:', error);
        });
}

// 請求用戶資料並更新頁面內容
function fetchUserInfo(token) {
    fetch('http://localhost/fruitstore/getUserInfo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ token: token })
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('獲取用戶資訊失敗:', data.error);
                return;
            }

            const user_name = document.getElementById('user_name');
            const nameField = document.getElementById('取貨人');
            const phoneField = document.getElementById('連絡電話');
            const emailField = document.getElementById('取貨E-mail');
            const addressField = document.getElementById('取貨地址');

            if (user_name) user_name.textContent = data.name || '用戶';
            if (nameField) nameField.value = data.name || '';
            if (phoneField) phoneField.value = data.phone || '';
            if (emailField) emailField.value = data.email || '';
            if (addressField) addressField.value = data.address || '';
        })
        .catch(error => {
            console.error('系統錯誤，請稍後再試：', error);
        });
}

// 初始化導航和會員狀態
function initializeNavigation() {
    const token = localStorage.getItem('authToken');
    const memberCenterLink = document.getElementById('memberCenter');
    const navigationLinks = document.querySelectorAll('.navigation a');
    const cartLink = document.querySelector('.cart');

    // 更新會員中心連結
    if (memberCenterLink) {
        memberCenterLink.href = token ? 'user.html' : 'user_login.html';
    }

    // 更新導航連結與購物車狀態
    navigationLinks.forEach(link => {
        if (!token) {
            link.setAttribute('href', 'user_login.html');
            if (cartLink) {
                cartLink.href = 'cart_empty.html';
            }
        } else if (link === memberCenterLink) {
            link.setAttribute('href', 'user.html');
        }
    });

    // 如果存在 token，請求用戶資料
    if (token) {
        fetchUserInfo(token);
    }
}

// 初始化頁面邏輯
document.addEventListener('DOMContentLoaded', function () {
    initializeUserSession();
    initializeNavigation();
});

// 直接購買後跳轉到購物車頁面
function redirectToCart() {
    const form = document.getElementById('addCartForm');
    form.action = 'cart.html';
    form.submit();
}
