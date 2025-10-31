<script>
    // 全选/取消全选功能
    document.querySelectorAll('.select-all-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            document.querySelectorAll(`input[data-class-id="${classId}"]`).forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    });
    
    document.querySelectorAll('.deselect-all-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            document.querySelectorAll(`input[data-class-id="${classId}"]`).forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
    
    // 状态选择时添加颜色提示
    document.querySelectorAll('.status-select').forEach(select => {
        // 初始化颜色
        updateStatusColor(select);
        
        // 监听变化
        select.addEventListener('change', function() {
            updateStatusColor(this);
        });
    });
    
    function updateStatusColor(select) {
        const selectedValue = select.value;
        // 移除所有可能的状态类
        select.classList.remove('status-arrived', 'status-leave', 'status-other', 'status-danger', 'status-secondary');
        // 添加对应的状态类
        switch(selectedValue) {
            case '1':
                select.classList.add('status-arrived');
                break;
            case '2':
                select.classList.add('status-leave');
                break;
            case '3':
                select.classList.add('status-other');
                break;
            case '4':
                select.classList.add('status-danger');
                break;
            case '5':
                select.classList.add('status-secondary');
                break;
        }
    }
</script>